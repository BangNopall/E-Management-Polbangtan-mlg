<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Facades\Hash;

class UsersImport implements ToModel, WithBatchInserts, WithChunkReading, WithStartRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
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

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function startRow(): int
    {
        return 2;
    }
}
