<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Facades\Hash;

class UsersImport implements ToModel
{
    private $row = 0; // Counter to track the row number

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $this->row++; // Increment the row counter

        // Skip the first row
        if ($this->row === 1) {
            return null;
        }

        if (empty($row[0])) return null;

        $nim = $row[0];
        $email = !empty($row[3]) ? $row[3] : $nim . '@dummy.com';

        return new User([
            'nim' => $nim,
            'name' => $row[1] ?? 'Unknown',
            'prodi_id' => $row[2] ?? null,
            'email' => $email,
            'password' => Hash::make("password"), 
            'role_id' => 3, 
            'status' => 'didalam',
        ]);
    }
}
