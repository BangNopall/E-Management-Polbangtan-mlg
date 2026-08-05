<?php

namespace App\Http\Requests;

use App\Models\UkmMember;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreUkmJadwalRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) {
            return false;
        }

        // Admin (1) bypasses the per-UKM scope check.
        if ($user->role_id === User::ADMIN_ROLE_ID) {
            return true;
        }

        // Pelatih (4) may only create schedules for a UKM they are an
        // active pelatih of — prevents a pelatih of UKM A from creating
        // jadwal (and triggering the presensi fan-out) for UKM B.
        if ($user->role_id !== User::PELATIH_ROLE_ID) {
            return false;
        }

        // Route uses implicit model binding, so route('ukm') may already be
        // the resolved Ukm instance rather than a raw ID — normalize it.
        $ukmId = $this->route('ukm') instanceof \App\Models\Ukm
            ? $this->route('ukm')->id
            : $this->route('ukm');

        return UkmMember::where('ukm_id', $ukmId)
            ->where('user_id', $user->id)
            ->where('peran', 'pelatih')
            ->where('status', 'aktif')
            ->exists();
    }

    public function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'max:255'],
            'jenis' => ['required', 'in:latihan,kegiatan_wajib'],
            'tanggal' => ['required', 'date'],
            'mulai_acara' => ['required', 'date_format:H:i,H:i:s'],
            'selesai_acara' => ['required', 'date_format:H:i,H:i:s', 'after:mulai_acara'],
            'lokasi' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'judul.required' => 'Judul kegiatan wajib diisi.',
            'jenis.in' => 'Jenis kegiatan tidak valid.',
            'tanggal.required' => 'Tanggal kegiatan wajib diisi.',
            'mulai_acara.required' => 'Waktu mulai acara wajib diisi.',
            'selesai_acara.required' => 'Waktu selesai acara wajib diisi.',
            'selesai_acara.after' => 'Waktu selesai acara harus setelah waktu mulai.',
        ];
    }
}
