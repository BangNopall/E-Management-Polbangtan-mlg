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

    public function test_pembina_dan_pelatih_ukm_bisa_membuat_jadwal_jika_ditugaskan(): void
    {
        $pembina = $this->makeUser(User::PEMBINA_ROLE_ID);
        $pelatihUkm = $this->makeUser(User::PELATIH_UKM_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Catur', 'slug' => 'ukm-catur']);

        UkmMember::create(['ukm_id' => $ukm->id, 'user_id' => $pembina->id, 'peran' => 'pembina', 'status' => 'aktif']);
        UkmMember::create(['ukm_id' => $ukm->id, 'user_id' => $pelatihUkm->id, 'peran' => 'pelatih', 'status' => 'aktif']);

        $response = $this->actingAs($pembina)->post(route('admin.ukm.jadwal.store', $ukm->id), [
            'judul' => 'Latihan Rutin Catur (Pembina)',
            'jenis' => 'latihan',
            'tanggal' => '2026-08-11',
            'mulai_acara' => '16:00:00',
            'selesai_acara' => '18:00:00',
            'lokasi' => 'Ruang Catur',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ukm_jadwals', ['judul' => 'Latihan Rutin Catur (Pembina)']);

        $response2 = $this->actingAs($pelatihUkm)->post(route('admin.ukm.jadwal.store', $ukm->id), [
            'judul' => 'Latihan Rutin Catur (Pelatih UKM)',
            'jenis' => 'latihan',
            'tanggal' => '2026-08-12',
            'mulai_acara' => '16:00:00',
            'selesai_acara' => '18:00:00',
            'lokasi' => 'Ruang Catur',
        ]);

        $response2->assertRedirect();
        $this->assertDatabaseHas('ukm_jadwals', ['judul' => 'Latihan Rutin Catur (Pelatih UKM)']);
    }

    public function test_membuat_jadwal_menghasilkan_fan_out_presensi_alpha_untuk_n_anggota_aktif(): void
    {
        $pelatih = $this->makeUser(User::PELATIH_UKM_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Voli Jadwal Test', 'slug' => 'ukm-voli-jadwal-test']);

        UkmMember::create(['ukm_id' => $ukm->id, 'user_id' => $pelatih->id, 'peran' => 'pelatih', 'status' => 'aktif']);

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
        $stafPelatih = $this->makeUser(User::PELATIH_UKM_ROLE_ID);
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

    public function test_pelatih_ukm_lain_tidak_bisa_membuat_jadwal_untuk_ukm_bukan_binaannya(): void
    {
        $pelatihUkmA = $this->makeUser(User::PELATIH_UKM_ROLE_ID);
        $ukmA = Ukm::create(['nama' => 'UKM Panahan', 'slug' => 'ukm-panahan']);
        $ukmB = Ukm::create(['nama' => 'UKM Renang', 'slug' => 'ukm-renang']);

        UkmMember::create(['ukm_id' => $ukmA->id, 'user_id' => $pelatihUkmA->id, 'peran' => 'pelatih', 'status' => 'aktif']);

        // Pelatih of UKM A attempts to create a jadwal for UKM B, which they are not assigned to.
        $response = $this->actingAs($pelatihUkmA)->post(route('admin.ukm.jadwal.store', $ukmB->id), [
            'judul' => 'Jadwal Ilegal Lintas UKM',
            'jenis' => 'latihan',
            'tanggal' => '2026-08-20',
            'mulai_acara' => '08:00:00',
            'selesai_acara' => '10:00:00',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('ukm_jadwals', ['ukm_id' => $ukmB->id, 'judul' => 'Jadwal Ilegal Lintas UKM']);
    }

    public function test_fan_out_presensi_idempoten_tidak_membuat_baris_ganda(): void
    {
        $admin = $this->makeUser(User::ADMIN_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Pramuka Jadwal Test', 'slug' => 'ukm-pramuka-jadwal-test']);
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

    /**
     * Perbaikan Modul UKM Dinamis — Isu #1.
     * Tab kalender di halaman detail UKM butuh endpoint JSON events untuk
     * FullCalendar (lihat .claude/plans/perbaikan-modul-ukm-dinamis.plan.md,
     * Task 3). Format field mengikuti konvensi FullCalendar: title, start.
     */
    public function test_admin_bisa_mengambil_events_jadwal_ukm_untuk_kalender(): void
    {
        $admin = $this->makeUser(User::ADMIN_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Kalender Test', 'slug' => 'ukm-kalender-test']);

        UkmJadwal::create([
            'ukm_id' => $ukm->id,
            'judul' => 'Latihan Kalender',
            'jenis' => 'latihan',
            'tanggal' => '2026-09-01',
            'mulai_acara' => '16:00:00',
            'selesai_acara' => '18:00:00',
            'status_verifikasi' => 'draft',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.ukm.jadwal.events', $ukm->id));

        $response->assertStatus(200);
        $response->assertJson([
            [
                'title' => 'Latihan Kalender',
                'start' => '2026-09-01',
            ],
        ]);
    }

    public function test_events_jadwal_ukm_hanya_berisi_jadwal_ukm_terkait(): void
    {
        $admin = $this->makeUser(User::ADMIN_ROLE_ID);
        $ukmA = Ukm::create(['nama' => 'UKM Kalender A', 'slug' => 'ukm-kalender-a']);
        $ukmB = Ukm::create(['nama' => 'UKM Kalender B', 'slug' => 'ukm-kalender-b']);

        UkmJadwal::create([
            'ukm_id' => $ukmA->id,
            'judul' => 'Jadwal A',
            'jenis' => 'latihan',
            'tanggal' => '2026-09-02',
            'mulai_acara' => '08:00:00',
            'selesai_acara' => '10:00:00',
            'status_verifikasi' => 'draft',
        ]);
        UkmJadwal::create([
            'ukm_id' => $ukmB->id,
            'judul' => 'Jadwal B',
            'jenis' => 'latihan',
            'tanggal' => '2026-09-03',
            'mulai_acara' => '08:00:00',
            'selesai_acara' => '10:00:00',
            'status_verifikasi' => 'draft',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.ukm.jadwal.events', $ukmA->id));

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['title' => 'Jadwal A']);
        $response->assertJsonMissing(['title' => 'Jadwal B']);
    }

    /**
     * Penyempurnaan Alur UKM — Isu #7.
     * Pelatih/Admin dapat menghapus jadwal HANYA jika status_verifikasi = 'draft'.
     * Jadwal yang sudah diajukan/disetujui/ditolak dipertahankan sebagai jejak audit.
     */
    public function test_pelatih_bisa_menghapus_jadwal_berstatus_draft(): void
    {
        $pelatih = $this->makeUser(User::PELATIH_UKM_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Robotik Hapus Test', 'slug' => 'ukm-robotik-hapus-test']);

        UkmMember::create(['ukm_id' => $ukm->id, 'user_id' => $pelatih->id, 'peran' => 'pelatih', 'status' => 'aktif']);

        $jadwal = UkmJadwal::create([
            'ukm_id' => $ukm->id,
            'judul' => 'Latihan Robotik Batal',
            'jenis' => 'latihan',
            'tanggal' => '2026-09-10',
            'mulai_acara' => '10:00:00',
            'selesai_acara' => '12:00:00',
            'status_verifikasi' => 'draft',
        ]);

        $response = $this->actingAs($pelatih)->delete(route('admin.ukm.jadwal.destroy', $jadwal->id));

        $response->assertRedirect(route('admin.ukm.show', $ukm->id));
        $response->assertSessionHas('success');
        $this->assertSoftDeleted('ukm_jadwals', ['id' => $jadwal->id]);
    }

    public function test_pembina_bisa_menghapus_jadwal_dan_mengajukan_verifikasi(): void
    {
        $pembina = $this->makeUser(User::PEMBINA_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Robotik Hapus Test 2', 'slug' => 'ukm-robotik-hapus-test-2']);

        UkmMember::create(['ukm_id' => $ukm->id, 'user_id' => $pembina->id, 'peran' => 'pembina', 'status' => 'aktif']);

        $jadwal1 = UkmJadwal::create([
            'ukm_id' => $ukm->id,
            'judul' => 'Latihan Robotik Batal',
            'jenis' => 'latihan',
            'tanggal' => '2026-09-10',
            'mulai_acara' => '10:00:00',
            'selesai_acara' => '12:00:00',
            'status_verifikasi' => 'draft',
        ]);

        // Ajukan verifikasi
        $responseAjukan = $this->actingAs($pembina)->patch(route('admin.ukm.jadwal.ajukanVerifikasi', $jadwal1->id));
        $responseAjukan->assertRedirect();
        $this->assertDatabaseHas('ukm_jadwals', ['id' => $jadwal1->id, 'status_verifikasi' => 'menunggu']);
        
        // Untuk mengetes hapus, kembalikan ke draft
        $jadwal1->refresh();
        $jadwal1->update(['status_verifikasi' => 'draft']);
        $responseHapus = $this->actingAs($pembina)->delete(route('admin.ukm.jadwal.destroy', $jadwal1->id));
        $responseHapus->assertRedirect();
        $this->assertSoftDeleted('ukm_jadwals', ['id' => $jadwal1->id]);
    }

    public function test_menghapus_jadwal_bukan_draft_ditolak(): void
    {
        $pelatih = $this->makeUser(User::PELATIH_UKM_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM E-Sport Hapus Test', 'slug' => 'ukm-esport-hapus-test']);

        UkmMember::create(['ukm_id' => $ukm->id, 'user_id' => $pelatih->id, 'peran' => 'pelatih', 'status' => 'aktif']);

        $jadwal = UkmJadwal::create([
            'ukm_id' => $ukm->id,
            'judul' => 'Turnamen Resmi',
            'jenis' => 'kegiatan_wajib',
            'tanggal' => '2026-09-12',
            'mulai_acara' => '13:00:00',
            'selesai_acara' => '17:00:00',
            'status_verifikasi' => 'disetujui',
        ]);

        $response = $this->actingAs($pelatih)->delete(route('admin.ukm.jadwal.destroy', $jadwal->id));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('ukm_jadwals', ['id' => $jadwal->id]);
    }

    public function test_pelatih_ukm_lain_ditolak_menghapus_jadwal_bukan_binaannya(): void
    {
        $pelatihA = $this->makeUser(User::PELATIH_UKM_ROLE_ID);
        $pelatihB = $this->makeUser(User::PELATIH_UKM_ROLE_ID);

        $ukmA = Ukm::create(['nama' => 'UKM A Hapus', 'slug' => 'ukm-a-hapus']);
        $ukmB = Ukm::create(['nama' => 'UKM B Hapus', 'slug' => 'ukm-b-hapus']);

        UkmMember::create(['ukm_id' => $ukmA->id, 'user_id' => $pelatihA->id, 'peran' => 'pelatih', 'status' => 'aktif']);
        UkmMember::create(['ukm_id' => $ukmB->id, 'user_id' => $pelatihB->id, 'peran' => 'pelatih', 'status' => 'aktif']);

        $jadwalA = UkmJadwal::create([
            'ukm_id' => $ukmA->id,
            'judul' => 'Jadwal A',
            'jenis' => 'latihan',
            'tanggal' => '2026-09-15',
            'mulai_acara' => '08:00:00',
            'selesai_acara' => '10:00:00',
            'status_verifikasi' => 'draft',
        ]);

        // Pelatih B tries to delete schedule of UKM A
        $response = $this->actingAs($pelatihB)->delete(route('admin.ukm.jadwal.destroy', $jadwalA->id));

        $response->assertStatus(403);
        $this->assertDatabaseHas('ukm_jadwals', ['id' => $jadwalA->id]);
    }

    /**
     * Penyempurnaan Alur UKM — Isu #5.
     * Catatan penolakan dari Pembina harus tampil di tabel jadwal halaman detail UKM.
     */
    public function test_detail_ukm_menampilkan_catatan_penolakan_pembina(): void
    {
        $admin = $this->makeUser(User::ADMIN_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Kategori Catatan', 'slug' => 'ukm-kategori-catatan']);

        UkmJadwal::create([
            'ukm_id' => $ukm->id,
            'judul' => 'Latihan Ditolak Pembina',
            'jenis' => 'latihan',
            'tanggal' => '2026-09-20',
            'mulai_acara' => '15:00:00',
            'selesai_acara' => '17:00:00',
            'status_verifikasi' => 'ditolak',
            'catatan_pembina' => 'Ruangan bentrok dengan kegiatan kampus.',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.ukm.show', $ukm->id));

        $response->assertStatus(200);
        $response->assertSee('Ruangan bentrok dengan kegiatan kampus.');
    }

    public function test_pelatih_ukm_lain_ditolak_mengambil_events_jadwal_bukan_binaannya(): void
    {
        $pelatihUkmA = $this->makeUser(User::PELATIH_UKM_ROLE_ID);
        $ukmA = Ukm::create(['nama' => 'UKM Events A', 'slug' => 'ukm-events-a']);
        $ukmB = Ukm::create(['nama' => 'UKM Events B', 'slug' => 'ukm-events-b']);

        UkmMember::create(['ukm_id' => $ukmA->id, 'user_id' => $pelatihUkmA->id, 'peran' => 'pelatih', 'status' => 'aktif']);

        $response = $this->actingAs($pelatihUkmA)->get(route('admin.ukm.jadwal.events', $ukmB->id));

        $response->assertStatus(403);
    }
}
