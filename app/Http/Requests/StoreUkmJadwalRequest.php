<?php

namespace App\Http\Requests;

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

        // Admin (1) and Pelatih (4) can create UKM schedules
        return in_array($user->role_id, [User::ADMIN_ROLE_ID, User::PELATIH_ROLE_ID]);
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
