<?php

namespace App\Http\Requests;

use App\Models\UkmMember;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUkmMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $ukmId = $this->route('ukm');

        return [
            'user_id' => [
                'required',
                'exists:users,id',
                Rule::unique('ukm_members', 'user_id')->where(fn ($q) => $q->where('ukm_id', $ukmId)),
            ],
            'peran' => [
                'required',
                Rule::in(['anggota', 'pelatih', 'pembina']),
                function ($attribute, $value, $fail) {
                    $targetUser = User::find($this->input('user_id'));
                    if (! $targetUser) {
                        return;
                    }

                    if ($value === 'anggota' && $targetUser->role_id !== User::USER_ROLE_ID) {
                        $fail('Peran anggota hanya dapat diberikan kepada mahasiswa.');
                    }

                    if (in_array($value, ['pelatih', 'pembina']) && ! in_array($targetUser->role_id, [User::PELATIH_ROLE_ID, User::PEMBINA_ROLE_ID])) {
                        $fail('Peran ' . $value . ' hanya dapat diberikan kepada staf (Pelatih / Pembina).');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.unique' => 'Pengguna ini sudah terdaftar sebagai anggota pada UKM ini.',
        ];
    }
}
