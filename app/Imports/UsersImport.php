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

        return new User([
            'name' => $row[0], // Assuming the first column is 'name'
            'email' => $row[1], // Assuming the second column is 'email'
            'password' => Hash::make("password"), // Assuming the third column is 'password'
            'role_id' => 3, // Assuming the fourth column is 'role
            'status' => 'didalam', // Assuming the fifth column is 'status
            // Add more attributes as needed based on your Excel columns
        ]);
    }
}
