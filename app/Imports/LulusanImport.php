<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class LulusanImport implements ToCollection, WithHeadingRow
{
    public $deletedCount = 0;
    public $notFoundCount = 0;

    public function collection(Collection $rows)
    {
        $nims = [];
        foreach ($rows as $row) {
            if (isset($row['nim'])) {
                $nims[] = $row['nim'];
            }
        }

        $users = User::whereIn('nim', $nims)->get();
        $this->notFoundCount = count($nims) - $users->count();

        foreach ($users as $user) {
            // Delete related data manually to prevent orphans
            \Illuminate\Support\Facades\DB::table('ukm_members')->where('user_id', $user->id)->delete();
            \Illuminate\Support\Facades\DB::table('pengajuan_izins')->where('user_id', $user->id)->delete();
            \Illuminate\Support\Facades\DB::table('pelanggarans')->where('user_id', $user->id)->delete();
            
            // Delete the user
            $user->delete();
            $this->deletedCount++;
        }
    }
}
