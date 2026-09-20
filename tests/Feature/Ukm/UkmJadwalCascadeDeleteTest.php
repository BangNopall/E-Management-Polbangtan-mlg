<?php

namespace Tests\Feature\Ukm;

use App\Models\BlokRuangan;
use App\Models\Kelas;
use App\Models\LevelKelas;
use App\Models\Prodi;
use App\Models\Ukm;
use App\Models\UkmJadwal;
use App\Models\UkmMember;
use App\Models\UkmPresensi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UkmJadwalCascadeDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $student;
    protected Ukm $ukm;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role_id' => User::ADMIN_ROLE_ID,
            'name' => 'Admin UKM',
        ]);

        $prodi = Prodi::create(['prodi' => 'Teknologi']);
        $level = LevelKelas::create(['nama_level_kelas' => 'Tingkat 1']);
        $kelas = Kelas::create([
            'kelas' => 'TK-1',
            'nama_kelas' => 'TK-1',
            'prodi_id' => $prodi->id,
            'level_kelas_id' => $level->id,
        ]);
        $blok = BlokRuangan::create(['name' => 'Blok C']);

        $this->student = User::factory()->create([
            'role_id' => User::USER_ROLE_ID,
            'prodi_id' => $prodi->id,
            'kelas_id' => $kelas->id,
            'blok_ruangan_id' => $blok->id,
            'nim' => '654321',
            'no_hp' => '0811223344',
            'asal_daerah' => 'Malang',
            'no_kamar' => '10',
            'password' => bcrypt('password123'),
        ]);

        $this->ukm = Ukm::create([
            'nama' => 'UKM Robotika',
            'slug' => 'ukm-robotika',
            'deskripsi' => 'Club Robotika Polbangtan',
            'is_active' => true,
        ]);

        UkmMember::create([
            'ukm_id' => $this->ukm->id,
            'user_id' => $this->student->id,
            'peran' => 'anggota',
            'status' => 'aktif',
        ]);
    }

    public function test_penghapusan_jadwal_ukm_menghapus_presensi_terkait(): void
    {
        $jadwal = UkmJadwal::create([
            'ukm_id' => $this->ukm->id,
            'judul' => 'Latihan Rutin Drone',
            'jenis' => 'latihan',
            'tanggal' => now()->toDateString(),
            'mulai_acara' => '15:00:00',
            'selesai_acara' => '17:00:00',
            'status_verifikasi' => 'draft',
        ]);

        $presensi = UkmPresensi::create([
            'ukm_jadwal_id' => $jadwal->id,
            'user_id' => $this->student->id,
            'status_kehadiran' => 'Alpha',
        ]);

        // Hapus jadwal melalui endpoint destroy
        $response = $this->actingAs($this->admin)->delete(route('admin.ukm.jadwal.destroy', $jadwal->id));
        $response->assertRedirect(route('admin.ukm.show', $this->ukm->id));
        $response->assertSessionHas('success');

        // Pastikan jadwal terhapus
        $this->assertSoftDeleted('ukm_jadwals', ['id' => $jadwal->id]);

        // Pastikan presensi juga terhapus (soft deleted)
        $this->assertSoftDeleted('ukm_presensis', ['id' => $presensi->id]);
    }

    public function test_riwayat_presensi_mahasiswa_tidak_menampilkan_jadwal_yang_dihapus(): void
    {
        $jadwal = UkmJadwal::create([
            'ukm_id' => $this->ukm->id,
            'judul' => 'Latihan Persiapan Lomba Robot',
            'jenis' => 'latihan',
            'tanggal' => now()->toDateString(),
            'mulai_acara' => '15:00:00',
            'selesai_acara' => '17:00:00',
            'status_verifikasi' => 'draft',
        ]);

        UkmPresensi::create([
            'ukm_jadwal_id' => $jadwal->id,
            'user_id' => $this->student->id,
            'status_kehadiran' => 'Alpha',
        ]);

        // Hapus jadwal
        $this->actingAs($this->admin)->delete(route('admin.ukm.jadwal.destroy', $jadwal->id));

        // Flush flash session sebelum login sebagai mahasiswa
        $this->flushSession();

        // Buka riwayat UKM sebagai mahasiswa
        $response = $this->actingAs($this->student)->get(route('home.ukm.riwayat'));
        $response->assertStatus(200);

        // Judul jadwal yang dihapus TIDAK BOLEH muncul di riwayat mahasiswa
        $response->assertDontSee('Latihan Persiapan Lomba Robot');

        // Dan halaman riwayat ukm dedicated juga tidak menampilkan
        $response2 = $this->actingAs($this->student)->get(route('home.ukm.riwayatAbsen'));
        $response2->assertStatus(200);
        $response2->assertDontSee('Latihan Persiapan Lomba Robot');
    }
}
