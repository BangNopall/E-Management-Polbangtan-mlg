<?php

namespace App\Http\Requests;

use App\Models\JenisIzin;
use App\Models\PengajuanIzin;
use App\Models\UkmMember;
use App\Models\User;
use App\Services\Izin\ApproverResolver;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePengajuanIzinRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && $user->role_id === User::USER_ROLE_ID;
    }

    public function rules(): array
    {
        return [
            'jenis_izin_id' => ['required', 'integer', 'exists:jenis_izins,id'],
            'ukm_id' => ['nullable', 'integer', 'exists:ukms,id'],
            'keperluan' => ['required', 'string', 'max:1000'],
            'tujuan_lokasi' => ['required', 'string', 'max:255'],
            'alamat_tujuan' => ['nullable', 'string', 'max:1000'],
            'waktu_berangkat' => ['required', 'date', 'after:now'],
            'waktu_kembali' => ['required', 'date', 'after:waktu_berangkat'],
        ];
    }

    public function messages(): array
    {
        return [
            'jenis_izin_id.required' => 'Jenis izin wajib dipilih.',
            'jenis_izin_id.exists' => 'Jenis izin yang dipilih tidak ditemukan.',
            'ukm_id.exists' => 'UKM yang dipilih tidak ditemukan.',
            'keperluan.required' => 'Keperluan perizinan wajib diisi.',
            'tujuan_lokasi.required' => 'Tujuan lokasi wajib diisi.',
            'waktu_berangkat.required' => 'Waktu keberangkatan wajib diisi.',
            'waktu_berangkat.after' => 'Waktu keberangkatan harus setelah waktu saat ini.',
            'waktu_kembali.required' => 'Waktu kembali wajib diisi.',
            'waktu_kembali.after' => 'Waktu kembali harus setelah waktu keberangkatan.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $user = $this->user();
            if (!$user) {
                return;
            }

            // Gerbang 1: Profil Lengkap
            $user->loadMissing('kelas');
            if (
                !$user->prodi_id ||
                !$user->kelas_id ||
                !$user->blok_ruangan_id
            ) {
                $v->errors()->add(
                    'profil',
                    'Profil Anda belum lengkap (Prodi, Kelas, atau Blok Ruangan belum terdata). Silakan hubungi admin asrama.'
                );
                return;
            }

            // Gerbang 2: Waktu Izin Tidak Boleh Beririsan (Overlapping)
            $waktuBerangkatStr = $this->input('waktu_berangkat');
            $waktuKembaliStr = $this->input('waktu_kembali');

            if ($waktuBerangkatStr && $waktuKembaliStr) {
                $waktuBerangkat = Carbon::parse($waktuBerangkatStr);
                $waktuKembali = Carbon::parse($waktuKembaliStr);

                $izinAktifExist = PengajuanIzin::where('user_id', $user->id)
                    ->whereIn('status', ['draft', 'diajukan', 'menunggu', 'disetujui', 'berjalan'])
                    ->where(function ($q) use ($waktuBerangkat, $waktuKembali) {
                        $q->where('waktu_berangkat', '<', $waktuKembali)
                          ->where('waktu_kembali', '>', $waktuBerangkat);
                    })
                    ->exists();

                if ($izinAktifExist) {
                    $v->errors()->add(
                        'status',
                        'Anda memiliki pengajuan izin lain yang beririsan rentang waktunya.'
                    );
                    return;
                }
            }

            // Ambillah JenisIzin
            $jenisIzinId = $this->input('jenis_izin_id');
            if (!$jenisIzinId) {
                return;
            }

            $jenisIzin = JenisIzin::with(['steps' => fn ($q) => $q->orderBy('urutan', 'asc')])->find($jenisIzinId);

            // Gerbang 3: Jenis Izin Aktif
            if (!$jenisIzin || !$jenisIzin->is_active) {
                $v->errors()->add('jenis_izin_id', 'Jenis izin yang dipilih sedang tidak aktif.');
                return;
            }

            // Gerbang 4: Syarat Spesifik Jenis (butuh_ukm)
            $ukmId = $this->input('ukm_id');
            if ($jenisIzin->butuh_ukm) {
                if (!$ukmId) {
                    $v->errors()->add('ukm_id', 'Pilihan UKM wajib diisi untuk jenis izin ini.');
                    return;
                }

                $isMember = UkmMember::where('ukm_id', $ukmId)
                    ->where('user_id', $user->id)
                    ->where('status', 'aktif')
                    ->exists();

                if (!$isMember) {
                    $v->errors()->add('ukm_id', 'Anda tidak terdaftar sebagai anggota aktif pada UKM yang dipilih.');
                    return;
                }
            }

            // Evaluasi waktu
            $waktuBerangkatStr = $this->input('waktu_berangkat');
            $waktuKembaliStr = $this->input('waktu_kembali');

            if ($waktuBerangkatStr && $waktuKembaliStr) {
                $waktuBerangkat = Carbon::parse($waktuBerangkatStr);
                $waktuKembali = Carbon::parse($waktuKembaliStr);

                // Gerbang 5: Minimal Lead Time (min_ajukan_jam)
                if ($jenisIzin->min_ajukan_jam > 0) {
                    $minTime = now()->addHours($jenisIzin->min_ajukan_jam);
                    if ($waktuBerangkat->lt($minTime)) {
                        $v->errors()->add(
                            'waktu_berangkat',
                            "Pengajuan izin jenis ini minimal dilakukan {$jenisIzin->min_ajukan_jam} jam sebelum waktu keberangkatan."
                        );
                    }
                }

                // Gerbang 6: Maksimal Durasi (maks_durasi_jam)
                if ($jenisIzin->maks_durasi_jam > 0) {
                    $durasiJam = $waktuBerangkat->diffInHours($waktuKembali);
                    if ($durasiJam > $jenisIzin->maks_durasi_jam) {
                        $v->errors()->add(
                            'waktu_kembali',
                            "Durasi izin melebihi batas maksimal yang diperbolehkan ({$jenisIzin->maks_durasi_jam} jam)."
                        );
                    }
                }
            }

            // Gerbang 8: Ketersediaan Approver (Dry-run ApproverResolver)
            $resolver = app(ApproverResolver::class);
            $context = [
                'user' => $user,
                'ukm_id' => $ukmId,
                'waktu_berangkat' => $waktuBerangkatStr,
            ];

            foreach ($jenisIzin->steps as $step) {
                if ($step->resolve_saat === 'submit') {
                    $result = $resolver->resolve($step, $context);
                    if ($result['candidates']->isEmpty()) {
                        $v->errors()->add(
                            'approver',
                            "Rantai penandatangan untuk '{$step->label}' tidak dapat ditemukan. Silakan hubungi admin asrama."
                        );
                        break;
                    }
                }
            }
        });
    }
}
