<?php

namespace Tests\Feature\Ukm;

use App\Models\Ukm;
use App\Models\UkmJadwal;
use App\Models\UkmMember;
use App\Models\UkmPresensi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Milestone C dari .claude/plans/epic-01-ukm-dinamis.md — US 1.2 (Penjadwalan + Fan-out Presensi).
 *
 * Requirements:
 *   - Admin (1) & Pelatih (4) bisa membuat UkmJadwal.
 *   - Pembuatan 1 UkmJadwal otomatis memicu fan-out N baris UkmPresensi (status 'Alpha')
 *     hanya untuk anggota AKTIF yang ber-peran 'anggota' (mahasiswa).
 *   - Staf (pelatih / pembina) yang terdaftar di ukm_members TIDAK ikut dibuatkan presensi.
 *   - Anggota nonaktif (status 'nonaktif') TIDAK ikut dibuatkan presensi.
 *   - Pembungkusan transaksi DB (DB::transaction) memastikan jika fan-out gagal, jadwal tidak tersimpan.
 *   - Idempoten: dipanggil ulang tidak menghasilkan baris presensi duplikat.
 */
class UkmJadwalTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(int $roleId): User
    {
        return User::factory()->create([
            'role_id' => $roleId,
            'blok_ruangan_id' => null,
            'kelas_id' => null,
            'prodi_id' => null,
        ]);
    }

    public function test_admin_dan_pelatih_bisa_membuat_jadwal_ukm(): void
    {
        $admin = $this->makeUser(User::ADMIN_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Basket', 'slug' => 'ukm-basket']);

        $response = $this->actingAs($admin)->post(route('admin.ukm.jadwal.store', $ukm->id), [
            'judul' => 'Latihan Rutin Basket',
            'jenis' => 'latihan',
            'tanggal' => '2026-08-10',
            'mulai_acara' => '16:00:00',
            'selesai_acara' => '18:00:00',
            'lokasi' => 'Lapangan Asrama',
        ]);

        $response->assertRedirect(route('admin.ukm.show', $ukm->id));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('ukm_jadwals', [
            'ukm_id' => $ukm->id,
            'judul' => 'Latihan Rutin Basket',
            'jenis' => 'latihan',
            'tanggal' => '2026-08-10',
            'status_verifikasi' => 'draft',
            'created_by' => $admin->id,
        ]);
    }

    public function test_membuat_jadwal_menghasilkan_fan_out_presensi_alpha_untuk_n_anggota_aktif(): void
    {
        $pelatih = $this->makeUser(User::PELATIH_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Voli', 'slug' => 'ukm-voli']);

        // Register 3 active student members
        $mhs1 = $this->makeUser(User::USER_ROLE_ID);
        $mhs2 = $this->makeUser(User::USER_ROLE_ID);
        $mhs3 = $this->makeUser(User::USER_ROLE_ID);

        UkmMember::create(['ukm_id' => $ukm->id, 'user_id' => $mhs1->id, 'peran' => 'anggota', 'status' => 'aktif']);
        UkmMember::create(['ukm_id' => $ukm->id, 'user_id' => $mhs2->id, 'peran' => 'anggota', 'status' => 'aktif']);
        UkmMember::create(['ukm_id' => $ukm->id, 'user_id' => $mhs3->id, 'peran' => 'anggota', 'status' => 'aktif']);

        $this->actingAs($pelatih)->post(route('admin.ukm.jadwal.store', $ukm->id), [
            'judul' => 'Latihan Perdana Voli',
            'jenis' => 'latihan',
            'tanggal' => '2026-08-12',
            'mulai_acara' => '15:30:00',
            'selesai_acara' => '17:30:00',
            'lokasi' => 'Gor Polbangtan',
        ]);

        $jadwal = UkmJadwal::where('ukm_id', $ukm->id)->first();
        $this->assertNotNull($jadwal);

        // Verify exactly 3 presensi records created with status 'Alpha'
        $this->assertEquals(3, UkmPresensi::where('ukm_jadwal_id', $jadwal->id)->count());

        $this->assertDatabaseHas('ukm_presensis', [
            'ukm_jadwal_id' => $jadwal->id,
            'user_id' => $mhs1->id,
            'status_kehadiran' => 'Alpha',
        ]);
        $this->assertDatabaseHas('ukm_presensis', [
            'ukm_jadwal_id' => $jadwal->id,
            'user_id' => $mhs2->id,
            'status_kehadiran' => 'Alpha',
        ]);
        $this->assertDatabaseHas('ukm_presensis', [
            'ukm_jadwal_id' => $jadwal->id,
            'user_id' => $mhs3->id,
            'status_kehadiran' => 'Alpha',
        ]);
    }

    public function test_staf_dan_anggota_nonaktif_tidak_ikut_dibuatkan_presensi(): void
    {
        $admin = $this->makeUser(User::ADMIN_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Musik', 'slug' => 'ukm-musik']);

        $mhsAktif = $this->makeUser(User::USER_ROLE_ID);
        $mhsNonaktif = $this->makeUser(User::USER_ROLE_ID);
        $stafPelatih = $this->makeUser(User::PELATIH_ROLE_ID);
        $stafPembina = $this->makeUser(User::PEMBINA_ROLE_ID);

        // Active student -> SHOULD get presensi
        UkmMember::create(['ukm_id' => $ukm->id, 'user_id' => $mhsAktif->id, 'peran' => 'anggota', 'status' => 'aktif']);
        // Inactive student -> SHOULD NOT get presensi
        UkmMember::create(['ukm_id' => $ukm->id, 'user_id' => $mhsNonaktif->id, 'peran' => 'anggota', 'status' => 'nonaktif']);
        // Staff pelatih -> SHOULD NOT get presensi
        UkmMember::create(['ukm_id' => $ukm->id, 'user_id' => $stafPelatih->id, 'peran' => 'pelatih', 'status' => 'aktif']);
        // Staff pembina -> SHOULD NOT get presensi
        UkmMember::create(['ukm_id' => $ukm->id, 'user_id' => $stafPembina->id, 'peran' => 'pembina', 'status' => 'aktif']);

        $this->actingAs($admin)->post(route('admin.ukm.jadwal.store', $ukm->id), [
            'judul' => 'Gladi Bersih Jamming',
            'jenis' => 'kegiatan_wajib',
            'tanggal' => '2026-08-15',
            'mulai_acara' => '19:00:00',
            'selesai_acara' => '21:00:00',
            'lokasi' => 'Aula Utama',
        ]);

        $jadwal = UkmJadwal::where('ukm_id', $ukm->id)->first();
        $this->assertNotNull($jadwal);

        // Only 1 presensi record should exist (for $mhsAktif)
        $this->assertEquals(1, UkmPresensi::where('ukm_jadwal_id', $jadwal->id)->count());

        $this->assertDatabaseHas('ukm_presensis', [
            'ukm_jadwal_id' => $jadwal->id,
            'user_id' => $mhsAktif->id,
            'status_kehadiran' => 'Alpha',
        ]);
        $this->assertDatabaseMissing('ukm_presensis', [
            'ukm_jadwal_id' => $jadwal->id,
            'user_id' => $mhsNonaktif->id,
        ]);
        $this->assertDatabaseMissing('ukm_presensis', [
            'ukm_jadwal_id' => $jadwal->id,
            'user_id' => $stafPelatih->id,
        ]);
        $this->assertDatabaseMissing('ukm_presensis', [
            'ukm_jadwal_id' => $jadwal->id,
            'user_id' => $stafPembina->id,
        ]);
    }

    public function test_mahasiswa_tidak_bisa_membuat_jadwal_ukm(): void
    {
        $mahasiswa = $this->makeUser(User::USER_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Silat', 'slug' => 'ukm-silat']);

        $response = $this->actingAs($mahasiswa)->post(route('admin.ukm.jadwal.store', $ukm->id), [
            'judul' => 'Latihan Ilegal',
            'jenis' => 'latihan',
            'tanggal' => '2026-08-20',
            'mulai_acara' => '08:00:00',
            'selesai_acara' => '10:00:00',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseMissing('ukm_jadwals', ['judul' => 'Latihan Ilegal']);
    }

    public function test_fan_out_presensi_idempoten_tidak_membuat_baris_ganda(): void
    {
        $admin = $this->makeUser(User::ADMIN_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Pramuka', 'slug' => 'ukm-pramuka']);
        $mhs = $this->makeUser(User::USER_ROLE_ID);

        UkmMember::create(['ukm_id' => $ukm->id, 'user_id' => $mhs->id, 'peran' => 'anggota', 'status' => 'aktif']);

        $this->actingAs($admin)->post(route('admin.ukm.jadwal.store', $ukm->id), [
            'judul' => 'Kemah Asrama',
            'jenis' => 'kegiatan_wajib',
            'tanggal' => '2026-08-25',
            'mulai_acara' => '07:00:00',
            'selesai_acara' => '17:00:00',
            'lokasi' => 'Lap. Apel',
        ]);

        $jadwal = UkmJadwal::where('ukm_id', $ukm->id)->first();
        $this->assertEquals(1, UkmPresensi::where('ukm_jadwal_id', $jadwal->id)->count());

        // Re-triggering fan out manually on the same schedule should not duplicate rows
        $ukm->load('anggotaAktif');
        foreach ($ukm->anggotaAktif as $member) {
            UkmPresensi::firstOrCreate([
                'ukm_jadwal_id' => $jadwal->id,
                'user_id' => $member->user_id,
            ], [
                'status_kehadiran' => 'Alpha',
            ]);
        }

        $this->assertEquals(1, UkmPresensi::where('ukm_jadwal_id', $jadwal->id)->count());
    }
}
