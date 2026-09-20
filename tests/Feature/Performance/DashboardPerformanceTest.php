<?php

namespace Tests\Feature\Performance;

use App\Models\BlokRuangan;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardPerformanceTest extends TestCase
{
    public function test_halaman_data_mahasiswa_bebas_dari_n_plus_one_query(): void
    {
        $admin = User::where('role_id', User::ADMIN_ROLE_ID)->firstOrFail();

        // Pastikan ada beberapa mahasiswa dengan kelas dan blok ruangan
        $kelas = Kelas::first() ?? Kelas::create(['nama_kelas' => '1A']);
        $blok = BlokRuangan::first() ?? BlokRuangan::create(['name' => 'Blok A']);

        User::where('role_id', User::USER_ROLE_ID)->take(10)->update([
            'kelas_id' => $kelas->id,
            'blok_ruangan_id' => $blok->id,
        ]);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->actingAs($admin)->get(route('admin.dataMahasiswa'));

        $response->assertStatus(200);

        $queries = DB::getQueryLog();
        $queryCount = count($queries);

        // Jika terjadi N+1 query problem, total query akan melebihi 20-30 query.
        // Dengan eager loading ['dosenPa', 'kelas', 'blok'], total query tidak lebih dari 8 query.
        $this->assertLessThanOrEqual(8, $queryCount, "Data Mahasiswa executed {$queryCount} queries, indicating N+1 query problem.");
    }

    public function test_kolom_is_password_changed_ada_di_tabel_users(): void
    {
        $user = User::where('role_id', User::USER_ROLE_ID)->firstOrFail();
        $this->assertArrayHasKey('is_password_changed', $user->toArray(), 'Column is_password_changed must exist on users table');
    }
}
