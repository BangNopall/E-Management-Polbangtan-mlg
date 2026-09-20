<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;

class DosenPaImport implements ToModel, WithHeadingRow, WithChunkReading, WithBatchInserts
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
        // Example headings: nama, email, nip (as nim/identifier), password
        $rawName = $row['nama'] ?? ($row['name'] ?? 'Unknown');
        $rawEmail = $row['email'] ?? null;
        $rawNip = $row['nip'] ?? null;

        return new User([
            'name'     => $this->sanitizeFormula((string) $rawName),
            'email'    => $this->sanitizeFormula((string) $rawEmail),
            'nim'      => $rawNip ? $this->sanitizeFormula((string) $rawNip) : null, 
            'password' => Hash::make($row['password'] ?? 'password'),
            'role_id'  => User::DOSEN_PA_ROLE_ID,
            'status'   => 'didalam', // default status
        ]);
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function batchSize(): int
    {
        return 100;
    }
}
