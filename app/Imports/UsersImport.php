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
    private function sanitizeFormula(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);
        if ($trimmed !== '' && in_array($trimmed[0], ['=', '+', '-', '@', "\t", "\r"])) {
            return "'" . $trimmed;
        }

        return $trimmed;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        if (empty($row[0])) return null;

        $nim = $this->sanitizeFormula((string) $row[0]);
        $rawName = isset($row[1]) ? (string) $row[1] : 'Unknown';
        $name = $this->sanitizeFormula($rawName);
        $email = !empty($row[3]) ? $this->sanitizeFormula((string) $row[3]) : str_replace('.', '', $nim) . '@ganti.email';

        return new User([
            'nim' => $nim,
            'name' => $name,
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
