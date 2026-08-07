<?php

namespace App\Services\Izin;

use App\Models\IzinApproval;
use App\Models\IzinWorkflowStep;
use App\Models\JenisIzin;
use App\Models\PengajuanIzin;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PengajuanIzinService
{
    public function __construct(
        protected ApproverResolver $resolver
    ) {}

    /**
     * Submit a permission request (PengajuanIzin).
     * Validates input parameters & approver chain resolution, snapshots student identity,
     * freezes approval chain steps, and initializes status to 'diajukan'.
     */
    public function ajukan(User $student, array $data): PengajuanIzin
    {
        return DB::transaction(function () use ($student, $data) {
            $student->loadMissing(['kelas', 'prodi', 'blok']);

            // 1. Validate JenisIzin
            $jenisIzin = JenisIzin::where('id', $data['jenis_izin_id'] ?? null)
                ->where('is_active', true)
                ->first();

            if (!$jenisIzin) {
                throw new InvalidArgumentException('Jenis izin tidak valid atau tidak aktif.');
            }

            $waktuBerangkat = Carbon::parse($data['waktu_berangkat']);
            $waktuKembali = Carbon::parse($data['waktu_kembali']);

            // 2. Validate dates
            if ($waktuKembali->lessThanOrEqualTo($waktuBerangkat)) {
                throw new InvalidArgumentException('Waktu kembali harus setelah waktu berangkat.');
            }

            // 3. Validate max duration
            if ($jenisIzin->maks_durasi_jam) {
                $durasiJam = $waktuBerangkat->diffInHours($waktuKembali);
                if ($durasiJam > $jenisIzin->maks_durasi_jam) {
                    throw new InvalidArgumentException("Durasi izin melebihi batas maksimal ({$jenisIzin->maks_durasi_jam} jam).");
                }
            }

            // 4. Validate min lead time (min_ajukan_jam)
            if ($jenisIzin->min_ajukan_jam) {
                $minWaktu = Carbon::now()->addHours($jenisIzin->min_ajukan_jam);
                if ($waktuBerangkat->lessThan($minWaktu)) {
                    throw new InvalidArgumentException("Pengajuan izin minimal dilakukan {$jenisIzin->min_ajukan_jam} jam sebelum keberangkatan.");
                }
            }

            // 5. Validate UKM requirement
            $ukmId = $data['ukm_id'] ?? null;
            if ($jenisIzin->butuh_ukm && !$ukmId) {
                throw new InvalidArgumentException('Pengajuan jenis izin ini wajib memilih UKM.');
            }

            // 6. Check for overlapping permission requests
            $overlapping = PengajuanIzin::where('user_id', $student->id)
                ->whereIn('status', ['diajukan', 'disetujui', 'berjalan'])
                ->where(function ($q) use ($waktuBerangkat, $waktuKembali) {
                    $q->whereBetween('waktu_berangkat', [$waktuBerangkat, $waktuKembali])
                        ->orWhereBetween('waktu_kembali', [$waktuBerangkat, $waktuKembali])
                        ->orWhere(function ($q2) use ($waktuBerangkat, $waktuKembali) {
                            $q2->where('waktu_berangkat', '<=', $waktuBerangkat)
                                ->where('waktu_kembali', '>=', $waktuKembali);
                        });
                })
                ->exists();

            if ($overlapping) {
                throw new InvalidArgumentException('Anda memiliki pengajuan izin lain yang beririsan rentang waktunya.');
            }

            // Fetch workflow steps
            $steps = $jenisIzin->steps;

            // Context object for resolution
            $context = [
                'user' => $student,
                'ukm_id' => $ukmId,
                'waktu_berangkat' => $waktuBerangkat->format('Y-m-d'),
            ];

            // 7. Validate eager steps (resolve_saat === 'submit')
            foreach ($steps as $step) {
                if ($this->shouldSkipStep($step, $data, $jenisIzin)) {
                    continue;
                }

                if ($step->resolve_saat === 'submit') {
                    $res = $this->resolver->resolve($step, $context);
                    if ($res['candidates']->isEmpty()) {
                        throw new InvalidArgumentException("Rantai penandatangan untuk '{$step->label}' tidak dapat ditemukan. Hubungi admin asrama.");
                    }
                }
            }

            // Create PengajuanIzin transaction record with snapshot data
            $pengajuan = PengajuanIzin::create([
                'user_id' => $student->id,
                'jenis_izin_id' => $jenisIzin->id,
                'ukm_id' => $ukmId,
                'keperluan' => $data['keperluan'],
                'tujuan_lokasi' => $data['tujuan_lokasi'],
                'alamat_tujuan' => $data['alamat_tujuan'] ?? null,
                'waktu_berangkat' => $waktuBerangkat,
                'waktu_kembali' => $waktuKembali,

                'nama_snapshot' => $student->name,
                'nirm_snapshot' => $student->nim,
                'kelas_snapshot' => optional($student->kelas)->nama_kelas,
                'prodi_snapshot' => optional($student->prodi)->prodi,
                'no_kamar_snapshot' => $student->no_kamar,
                'no_hp_snapshot' => $student->no_hp,

                'status' => 'diajukan',
                'langkah_aktif' => 1,
                'diajukan_at' => Carbon::now(),
            ]);

            // Freeze steps into izin_approvals
            foreach ($steps as $step) {
                $shouldSkip = $this->shouldSkipStep($step, $data, $jenisIzin);

                $approverUserId = null;
                $catatan = null;

                if ($shouldSkip) {
                    $status = 'dilewati';
                    $catatan = 'dilewati: kondisi langkah tidak terpenuhi';
                } else {
                    $status = 'menunggu';
                    if ($step->resolve_saat === 'submit') {
                        $res = $this->resolver->resolve($step, $context);
                        $candidates = $res['candidates'];
                        $approverUserId = $candidates->first()?->id;

                        if ($res['is_fallback']) {
                            $catatan = 'Fallback digunakan untuk langkah ini';
                        }
                    }
                }

                IzinApproval::create([
                    'pengajuan_izin_id' => $pengajuan->id,
                    'urutan' => $step->urutan,
                    'label_snapshot' => $step->label,
                    'blok_snapshot' => $step->blok,
                    'approver_user_id' => $approverUserId,
                    'approver_nama_snapshot' => $approverUserId ? User::find($approverUserId)?->name : null,
                    'mode' => $step->mode,
                    'status' => $status,
                    'catatan' => $catatan,
                    'dibuka_at' => ($step->urutan === 1 && !$shouldSkip) ? Carbon::now() : null,
                ]);
            }

            // Adjust active step if first step was skipped
            $firstActive = $pengajuan->approvals()->where('status', 'menunggu')->first();
            if ($firstActive) {
                $pengajuan->update(['langkah_aktif' => $firstActive->urutan]);
                if (!$firstActive->dibuka_at) {
                    $firstActive->update(['dibuka_at' => Carbon::now()]);
                }
            }

            return $pengajuan;
        });
    }

    /**
     * Approve current step.
     */
    public function setujui(IzinApproval $approval, User $actor, ?string $catatan = null): PengajuanIzin
    {
        return DB::transaction(function () use ($approval, $actor, $catatan) {
            $freshApproval = IzinApproval::whereKey($approval->id)->lockForUpdate()->firstOrFail();
            $pengajuan = PengajuanIzin::whereKey($freshApproval->pengajuan_izin_id)->lockForUpdate()->firstOrFail();

            abort_unless($freshApproval->status === 'menunggu', 409, 'Langkah ini sudah diputuskan oleh petugas lain.');
            abort_unless($freshApproval->urutan === $pengajuan->langkah_aktif, 409, 'Langkah sebelumnya belum selesai.');

            // Validate step candidate ownership
            $step = IzinWorkflowStep::where('jenis_izin_id', $pengajuan->jenis_izin_id)
                ->where('urutan', $freshApproval->urutan)
                ->first();

            $context = [
                'user' => $pengajuan->user,
                'ukm_id' => $pengajuan->ukm_id,
                'waktu_berangkat' => optional($pengajuan->waktu_berangkat)->format('Y-m-d'),
            ];

            if ($step && $step->resolve_saat === 'langkah_aktif') {
                $res = $this->resolver->resolve($step, $context);
                $candidates = $res['candidates'];
            } else {
                $candidates = $freshApproval->approver_user_id ? collect([User::find($freshApproval->approver_user_id)]) : collect();
                // If primary was empty, try resolving live
                if ($candidates->isEmpty() && $step) {
                    $res = $this->resolver->resolve($step, $context);
                    $candidates = $res['candidates'];
                }
            }

            abort_unless($candidates->pluck('id')->contains($actor->id), 403, 'Anda bukan penandatangan yang berhak untuk langkah ini.');

            // Update current approval step
            $freshApproval->update([
                'status' => 'disetujui',
                'approver_user_id' => $actor->id,
                'approver_nama_snapshot' => $actor->name,
                'acted_by' => $actor->id,
                'acted_at' => Carbon::now(),
                'acted_ip' => request()?->ip(),
                'acted_user_agent' => request()?->userAgent(),
                'catatan' => $catatan ?? $freshApproval->catatan,
            ]);

            // Determine next waiting step
            $nextApproval = $pengajuan->approvals()
                ->where('urutan', '>', $freshApproval->urutan)
                ->where('status', 'menunggu')
                ->first();

            if ($nextApproval) {
                $pengajuan->update(['langkah_aktif' => $nextApproval->urutan]);

                $nextStep = IzinWorkflowStep::where('jenis_izin_id', $pengajuan->jenis_izin_id)
                    ->where('urutan', $nextApproval->urutan)
                    ->first();

                if ($nextStep && $nextStep->resolve_saat === 'langkah_aktif') {
                    $resNext = $this->resolver->resolve($nextStep, $context);
                    $firstCand = $resNext['candidates']->first();
                    $nextApproval->update([
                        'approver_user_id' => $firstCand?->id,
                        'approver_nama_snapshot' => $firstCand?->name,
                        'dibuka_at' => Carbon::now(),
                        'catatan' => $resNext['is_fallback'] ? 'Fallback digunakan untuk langkah ini' : null,
                    ]);
                } else {
                    $nextApproval->update(['dibuka_at' => Carbon::now()]);
                }
            } else {
                // All steps approved -> Approve permission request completely
                $nomorSuratService = app(NomorSuratService::class);
                $pengajuan->update([
                    'status' => 'disetujui',
                    'disetujui_at' => Carbon::now(),
                    'langkah_aktif' => null,
                    'nomor_surat' => $nomorSuratService->generateNext(Carbon::now()),
                    'qr_token' => Str::random(40),
                ]);

                // Bebaskan presensi kegiatan beririsan: 'Alpha' -> 'Izin'
                app(PembebasanPresensiService::class)->bebaskanUntukPengajuan($pengajuan->fresh());
            }

            return $pengajuan->fresh(['approvals']);
        });
    }

    /**
     * Reject permission request at current step.
     */
    public function tolak(IzinApproval $approval, User $actor, string $alasan): PengajuanIzin
    {
        return DB::transaction(function () use ($approval, $actor, $alasan) {
            $freshApproval = IzinApproval::whereKey($approval->id)->lockForUpdate()->firstOrFail();
            $pengajuan = PengajuanIzin::whereKey($freshApproval->pengajuan_izin_id)->lockForUpdate()->firstOrFail();

            abort_unless($freshApproval->status === 'menunggu', 409, 'Langkah ini sudah diputuskan oleh petugas lain.');
            abort_unless($freshApproval->urutan === $pengajuan->langkah_aktif, 409, 'Langkah sebelumnya belum selesai.');

            $freshApproval->update([
                'status' => 'ditolak',
                'acted_by' => $actor->id,
                'acted_at' => Carbon::now(),
                'acted_ip' => request()?->ip(),
                'acted_user_agent' => request()?->userAgent(),
                'catatan' => $alasan,
            ]);

            // Mark remaining waiting steps as dilewati
            $pengajuan->approvals()
                ->where('urutan', '>', $freshApproval->urutan)
                ->where('status', 'menunggu')
                ->update(['status' => 'dilewati']);

            $pengajuan->update([
                'status' => 'ditolak',
                'alasan_penolakan' => $alasan,
                'langkah_aktif' => null,
            ]);

            return $pengajuan->fresh(['approvals']);
        });
    }

    /**
     * Cancel permission request by student.
     */
    public function batalkan(PengajuanIzin $pengajuan, User $student): PengajuanIzin
    {
        return DB::transaction(function () use ($pengajuan, $student) {
            $freshPengajuan = PengajuanIzin::whereKey($pengajuan->id)->lockForUpdate()->firstOrFail();

            abort_unless($freshPengajuan->user_id === $student->id, 403, 'Anda tidak berhak membatalkan pengajuan ini.');
            abort_unless(in_array($freshPengajuan->status, ['draft', 'diajukan']), 422, 'Pengajuan yang sudah diproses atau disetujui tidak dapat dibatalkan.');

            $freshPengajuan->approvals()
                ->where('status', 'menunggu')
                ->update(['status' => 'dilewati']);

            $freshPengajuan->update([
                'status' => 'dibatalkan',
                'langkah_aktif' => null,
            ]);

            return $freshPengajuan->fresh(['approvals']);
        });
    }

    /**
     * Determine if a workflow step should be skipped based on conditions.
     */
    private function shouldSkipStep(IzinWorkflowStep $step, array $data, JenisIzin $jenisIzin): bool
    {
        if ($step->kondisi === 'jika_ada_ukm') {
            return empty($data['ukm_id']);
        }

        if ($step->kondisi === 'jika_bermalam') {
            return !$jenisIzin->butuh_bermalam;
        }

        return false;
    }

    /**
     * Generate unique consecutive serial number for approved permission letter.
     */
    private function generateNomorSurat(PengajuanIzin $pengajuan): string
    {
        $year = Carbon::now()->format('Y');
        $monthRoman = $this->getRomanMonth((int) Carbon::now()->format('m'));

        $count = PengajuanIzin::whereYear('disetujui_at', $year)->whereNotNull('nomor_surat')->count() + 1;
        $sequence = str_pad((string) $count, 4, '0', STR_PAD_LEFT);

        $kodeForm = $pengajuan->jenisIzin->kode_form ?? 'IZIN';

        return "{$kodeForm}/{$sequence}/{$monthRoman}/{$year}";
    }

    private function getRomanMonth(int $month): string
    {
        $map = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
            5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
            9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];

        return $map[$month] ?? 'I';
    }

    /**
     * Catat scan gerbang untuk mahasiswa yang memiliki izin aktif.
     * Menangani transisi status (disetujui -> berjalan -> selesai / terlambat)
     * serta auto-buat Pelanggaran jika kembali melebihi tenggat izin.
     */
    public function catatScanGerbang(PengajuanIzin $izin, User $user, \Carbon\Carbon $now, string $statusTarget): PengajuanIzin
    {
        return DB::transaction(function () use ($izin, $user, $now, $statusTarget) {
            $freshIzin = PengajuanIzin::whereKey($izin->id)->lockForUpdate()->firstOrFail();

            // Mahasiswa melakukan scan KELUAR asrama
            if ($statusTarget === 'diluar' && $freshIzin->status === 'disetujui') {
                $freshIzin->update([
                    'status' => 'berjalan',
                    'keluar_at' => $now,
                ]);

                User::where('id', $user->id)->update(['status' => 'izin']);
            }

            // Mahasiswa melakukan scan MASUK kembali ke asrama
            if ($statusTarget === 'didalam' && in_array($freshIzin->status, ['disetujui', 'berjalan'])) {
                if ($now->gt($freshIzin->waktu_kembali)) {
                    // TERLAMBAT kembali
                    $freshIzin->update([
                        'status' => 'terlambat',
                        'kembali_at' => $now,
                    ]);

                    User::where('id', $user->id)->update(['status' => 'didalam']);

                    // Auto-buat Pelanggaran berstatus 'submitted' (BUKAN 'Done')
                    $this->buatPelanggaranKeterlambatan($freshIzin, $user, $now);
                } else {
                    // TEPAT WAKTU kembali
                    $freshIzin->update([
                        'status' => 'selesai',
                        'kembali_at' => $now,
                    ]);

                    User::where('id', $user->id)->update(['status' => 'didalam']);
                }
            }

            return $freshIzin->fresh();
        });
    }

    /**
     * Buat record Pelanggaran otomatis untuk keterlambatan kembali.
     * Status: 'submitted' (BUKAN 'Done').
     */
    private function buatPelanggaranKeterlambatan(PengajuanIzin $izin, User $user, \Carbon\Carbon $now): void
    {
        // Cari jenis pelanggaran "Terlambat kembali dari izin resmi"
        $jenisTerlambatIzin = \App\Models\JenisPelanggaran::where('jenis_pelanggaran', 'like', '%lambat kembali%')->first();

        if (!$jenisTerlambatIzin) {
            // Fallback: jika jenis belum ada, buat transparan
            $jenisTerlambatIzin = \App\Models\JenisPelanggaran::firstOrCreate(
                ['jenis_pelanggaran' => 'Terlambat kembali dari izin resmi'],
                [
                    'kategori_id' => 1,
                    'poin' => 2,
                    'sub_kategori' => 'Ringan',
                ]
            );
        }

        \App\Models\Pelanggaran::create([
            'user_id' => $user->id,
            'jenis_pelanggaran_id' => $jenisTerlambatIzin->id,
            'date' => $now->toDateString(),
            'time' => $now->toTimeString(),
            'statusPelanggaran' => 'submitted',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
