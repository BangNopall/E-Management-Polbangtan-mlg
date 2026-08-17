<?php

namespace Tests\Feature\Ukm;

use App\Models\Ukm;
use App\Models\UkmJadwal;
use App\Models\UkmMember;
use App\Models\UkmPresensi;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Milestone A dari .claude/plans/epic-01-ukm-dinamis.md.
 *
 * Menguji skema 4 tabel generik (ukms, ukm_members, ukm_jadwals, ukm_presensis)
 * sesuai §5.2 docs/design/DESIGN-Epic01-Modul-UKM-Dinamis.md: constraint unique
 * dan cascade delete adalah pengaman level-database yang tidak dimiliki modul
 * Kegiatan Wajib lama (presensi_apels dkk).
 *
 * Memakai RefreshDatabase (bukan pola KonselingHandoffTest/Laravel13SmokeTest
 * yang menembak DB dev tanpa transaksi) karena test ini menguji constraint
 * lewat insert langsung — butuh rollback bersih antar test. Lihat Konflik 2
 * di plan: koneksi DB tes diambil dari .env.testing (MySQL, bukan sqlite).
 */
class UkmSchemaTest extends TestCase
{
    use RefreshDatabase;

    // Role 1-5 datang dari DatabaseSeeder (Tests\TestCase::$seed = true) —
    // lihat komentar senada di PembinaRoleTest.php.

    /**
     * blok_ruangan_id/kelas_id/prodi_id di-null-kan (kolomnya nullable) supaya
     * test tidak perlu menyeed 3 tabel lookup lain hanya untuk memuaskan FK
     * dari UserFactory — test ini tidak menguji profil mahasiswa.
     */
    private function makeUser(int $roleId): User
    {
        return User::factory()->create([
            'role_id' => $roleId,
            'blok_ruangan_id' => null,
            'kelas_id' => null,
            'prodi_id' => null,
        ]);
    }

    public function test_ukm_nama_harus_unik(): void
    {
        Ukm::create(['nama' => 'UKM Pecinta Alam', 'slug' => 'ukm-pecinta-alam']);

        $this->expectException(QueryException::class);

        Ukm::create(['nama' => 'UKM Pecinta Alam', 'slug' => 'ukm-pecinta-alam-2']);
    }

    public function test_satu_user_tidak_bisa_terdaftar_dua_kali_di_ukm_yang_sama(): void
    {
        $ukm = Ukm::create(['nama' => 'UKM Silat', 'slug' => 'ukm-silat']);
        $mahasiswa = $this->makeUser(User::USER_ROLE_ID);

        UkmMember::create([
            'ukm_id' => $ukm->id,
            'user_id' => $mahasiswa->id,
            'peran' => 'anggota',
            'status' => 'aktif',
        ]);

        $this->expectException(QueryException::class);

        UkmMember::create([
            'ukm_id' => $ukm->id,
            'user_id' => $mahasiswa->id,
            'peran' => 'anggota',
            'status' => 'aktif',
        ]);
    }

    public function test_presensi_ganda_ditolak_di_level_database(): void
    {
        $ukm = Ukm::create(['nama' => 'UKM Renang', 'slug' => 'ukm-renang']);
        $jadwal = UkmJadwal::create([
            'ukm_id' => $ukm->id,
            'judul' => 'Latihan Rutin',
            'jenis' => 'latihan',
            'tanggal' => now()->toDateString(),
            'mulai_acara' => '15:00:00',
            'selesai_acara' => '17:00:00',
        ]);
        $mahasiswa = $this->makeUser(User::USER_ROLE_ID);

        UkmPresensi::create([
            'ukm_jadwal_id' => $jadwal->id,
            'user_id' => $mahasiswa->id,
            'status_kehadiran' => 'Alpha',
        ]);

        $this->expectException(QueryException::class);

        UkmPresensi::create([
            'ukm_jadwal_id' => $jadwal->id,
            'user_id' => $mahasiswa->id,
            'status_kehadiran' => 'Alpha',
        ]);
    }

    public function test_menghapus_ukm_menghapus_anggota_dan_jadwalnya(): void
    {
        $ukm = Ukm::create(['nama' => 'UKM Musik', 'slug' => 'ukm-musik']);
        $mahasiswa = $this->makeUser(User::USER_ROLE_ID);

        $member = UkmMember::create([
            'ukm_id' => $ukm->id,
            'user_id' => $mahasiswa->id,
            'peran' => 'anggota',
            'status' => 'aktif',
        ]);
        $jadwal = UkmJadwal::create([
            'ukm_id' => $ukm->id,
            'judul' => 'Latihan Band',
            'jenis' => 'latihan',
            'tanggal' => now()->toDateString(),
            'mulai_acara' => '19:00:00',
            'selesai_acara' => '21:00:00',
        ]);

        $ukm->delete();

        $this->assertDatabaseMissing('ukm_members', ['id' => $member->id]);
        $this->assertDatabaseMissing('ukm_jadwals', ['id' => $jadwal->id]);
    }

    public function test_menghapus_jadwal_menghapus_presensinya(): void
    {
        $ukm = Ukm::create(['nama' => 'UKM Basket', 'slug' => 'ukm-basket']);
        $jadwal = UkmJadwal::create([
            'ukm_id' => $ukm->id,
            'judul' => 'Latihan Tanding',
            'jenis' => 'latihan',
            'tanggal' => now()->toDateString(),
            'mulai_acara' => '16:00:00',
            'selesai_acara' => '18:00:00',
        ]);
        $mahasiswa = $this->makeUser(User::USER_ROLE_ID);
        $presensi = UkmPresensi::create([
            'ukm_jadwal_id' => $jadwal->id,
            'user_id' => $mahasiswa->id,
            'status_kehadiran' => 'Alpha',
        ]);

        $jadwal->delete();

        $this->assertDatabaseMissing('ukm_presensis', ['id' => $presensi->id]);
    }

    public function test_relasi_anggota_aktif_hanya_mengembalikan_peran_anggota_berstatus_aktif(): void
    {
        $ukm = Ukm::create(['nama' => 'UKM Panahan', 'slug' => 'ukm-panahan']);

        $anggotaAktif = $this->makeUser(User::USER_ROLE_ID);
        $anggotaNonaktif = $this->makeUser(User::USER_ROLE_ID);
        $pelatih = $this->makeUser(User::PELATIH_ROLE_ID);
        $pembina = $this->makeUser(User::PEMBINA_ROLE_ID);

        UkmMember::create(['ukm_id' => $ukm->id, 'user_id' => $anggotaAktif->id, 'peran' => 'anggota', 'status' => 'aktif']);
        UkmMember::create(['ukm_id' => $ukm->id, 'user_id' => $anggotaNonaktif->id, 'peran' => 'anggota', 'status' => 'nonaktif']);
        UkmMember::create(['ukm_id' => $ukm->id, 'user_id' => $pelatih->id, 'peran' => 'pelatih', 'status' => 'aktif']);
        UkmMember::create(['ukm_id' => $ukm->id, 'user_id' => $pembina->id, 'peran' => 'pembina', 'status' => 'aktif']);

        $hasilAnggotaAktif = $ukm->anggotaAktif()->pluck('user_id');

        $this->assertCount(1, $hasilAnggotaAktif);
        $this->assertTrue($hasilAnggotaAktif->contains($anggotaAktif->id));
        $this->assertFalse($hasilAnggotaAktif->contains($anggotaNonaktif->id));
        $this->assertFalse($hasilAnggotaAktif->contains($pelatih->id));
        $this->assertFalse($hasilAnggotaAktif->contains($pembina->id));
    }
}
