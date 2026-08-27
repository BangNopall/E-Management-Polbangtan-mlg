<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;

class DosenPaImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Example headings: nama, email, nip (as nim/identifier), password
        return new User([
            'name'     => $row['nama'] ?? $row['name'],
            'email'    => $row['email'],
            'nim'      => $row['nip'] ?? null, 
            'password' => Hash::make($row['password'] ?? 'password123'),
            'role_id'  => User::DOSEN_PA_ROLE_ID,
            'status'   => 'didalam', // default status
        ]);
    }
}
