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
 * Milestone F dari .claude/plans/epic-01-ukm-dinamis.md — Fitur & Halaman Mahasiswa (US 1.3).
 *
 * Requirements:
 *   - Route GET dashboard/ukm (home.ukm.index) & GET dashboard/ukm/riwayat (home.ukm.riwayat).
 *   - Akses di-gate role:user (role_id=3).
 *   - "UKM Saya" menampilkan daftar UKM yang diikuti mahasiswa beserta jadwal terdekat.
 *   - "Riwayat Presensi UKM" menampilkan log presensi UKM mahasiswa (Hadir, Alpha, Izin).
 */
class UkmMahasiswaTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(int $roleId): User
    {
        return User::factory()->create([
            'role_id' => $roleId,
            'blok_ruangan_id' => 1,
            'kelas_id' => 1,
            'prodi_id' => 1,
            'no_kamar' => '01',
            'no_hp' => '08123',
            'asal_daerah' => 'Malang',
            'password' => bcrypt('password123'),
        ]);
    }

    public function test_mahasiswa_bisa_mengakses_halaman_ukm_saya(): void
    {
        $mahasiswa = $this->makeUser(User::USER_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Pramuka Mahasiswa Test', 'slug' => 'ukm-pramuka-mahasiswa-test']);

        UkmMember::create([
            'ukm_id' => $ukm->id,
            'user_id' => $mahasiswa->id,
            'peran' => 'anggota',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($mahasiswa)->get(route('home.ukm.index'));

        $response->assertStatus(200);
        $response->assertViewIs('ukm.index');
        $response->assertSee('UKM Pramuka Mahasiswa Test');
    }

    /**
     * Penyempurnaan Alur UKM — Isu #4b.
     * Mahasiswa hanya boleh melihat jadwal yang sudah sah (disetujui Pembina).
     * Jadwal draft/menunggu/ditolak belum tentu jadi, menampilkannya berisiko
     * membuat mahasiswa datang ke kegiatan yang batal.
     */
    public function test_jadwal_belum_disetujui_tidak_tampil_di_jadwal_mendatang_mahasiswa(): void
    {
        $mahasiswa = $this->makeUser(User::USER_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Panahan', 'slug' => 'ukm-panahan']);

        UkmMember::create([
            'ukm_id' => $ukm->id,
            'user_id' => $mahasiswa->id,
            'peran' => 'anggota',
            'status' => 'aktif',
        ]);

        $besok = now()->addDay()->toDateString();

        foreach (['disetujui', 'draft', 'menunggu', 'ditolak'] as $status) {
            UkmJadwal::create([
                'ukm_id' => $ukm->id,
                'judul' => 'Latihan ' . ucfirst($status),
                'jenis' => 'latihan',
                'tanggal' => $besok,
                'mulai_acara' => '16:00:00',
                'selesai_acara' => '18:00:00',
                'status_verifikasi' => $status,
            ]);
        }

        $response = $this->actingAs($mahasiswa)->get(route('home.ukm.index'));

        $response->assertStatus(200);
        $response->assertSee('Latihan Disetujui');
        $response->assertDontSee('Latihan Draft');
        $response->assertDontSee('Latihan Menunggu');
        $response->assertDontSee('Latihan Ditolak');
    }

    /**
     * Penyempurnaan Alur UKM — Isu #6.
     * Jadwal mendatang harus memuat lokasi dan rentang jam mulai–selesai,
     * bukan hanya tanggal + jam mulai, supaya mahasiswa tahu harus datang
     * ke mana dan sampai jam berapa.
     */
    public function test_jadwal_mendatang_menampilkan_lokasi_dan_jam_mulai_sampai_selesai(): void
    {
        $mahasiswa = $this->makeUser(User::USER_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Paduan Suara', 'slug' => 'ukm-paduan-suara']);

        UkmMember::create([
            'ukm_id' => $ukm->id,
            'user_id' => $mahasiswa->id,
            'peran' => 'anggota',
            'status' => 'aktif',
        ]);

        UkmJadwal::create([
            'ukm_id' => $ukm->id,
            'judul' => 'Gladi Bersih Wisuda',
            'jenis' => 'kegiatan_wajib',
            'tanggal' => now()->addDay()->toDateString(),
            'mulai_acara' => '13:00:00',
            'selesai_acara' => '15:30:00',
            'lokasi' => 'Aula Gedung Serbaguna',
            'status_verifikasi' => 'disetujui',
        ]);

        $response = $this->actingAs($mahasiswa)->get(route('home.ukm.index'));

        $response->assertStatus(200);
        $response->assertSee('Aula Gedung Serbaguna');
        $response->assertSee('13:00:00');
        $response->assertSee('15:30:00');
    }

    public function test_mahasiswa_bisa_melihat_riwayat_presensi_ukm(): void
    {
        $mahasiswa = $this->makeUser(User::USER_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Voli Mahasiswa Test', 'slug' => 'ukm-voli-mahasiswa-test']);

        UkmMember::create([
            'ukm_id' => $ukm->id,
            'user_id' => $mahasiswa->id,
            'peran' => 'anggota',
            'status' => 'aktif',
        ]);

        $jadwal = UkmJadwal::create([
            'ukm_id' => $ukm->id,
            'judul' => 'Latihan Rutin Voli',
            'jenis' => 'latihan',
            'tanggal' => '2026-08-10',
            'mulai_acara' => '16:00:00',
            'selesai_acara' => '18:00:00',
            'status_verifikasi' => 'disetujui',
        ]);

        UkmPresensi::create([
            'ukm_jadwal_id' => $jadwal->id,
            'user_id' => $mahasiswa->id,
            'status_kehadiran' => 'Hadir',
            'jam_kehadiran' => '16:15:00',
        ]);

        $response = $this->actingAs($mahasiswa)->get(route('home.ukm.riwayat'));

        $response->assertStatus(200);
        $response->assertViewIs('ukm.riwayat');
        $response->assertSee('Latihan Rutin Voli');
        $response->assertSee('Hadir');
    }
}
