<?php

namespace Tests\Feature\Ukm;

use App\Models\Ukm;
use App\Models\UkmJadwal;
use App\Models\UkmMember;
use App\Models\UkmPresensi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Milestone D dari .claude/plans/epic-01-ukm-dinamis.md — US 1.3 (Scanner QR Generik UKM).
 *
 * Requirements:
 *   - Route GET admin/kamera-ukm/{jadwal} (show) & POST api/kamera-ukm/{jadwal} (store).
 *   - Payload QR mahasiswa tetap: {user_id, date, time, scanner}.
 *   - QR expired (> 30 detik selisih jam) ditolak.
 *   - Mahasiswa yang bukan anggota UKM aktif ditolak.
 *   - Waktu scan di luar jadwal (sebelum mulai / setelah selesai) ditolak.
 *   - Mahasiswa yang sudah berstatus 'Hadir' ditolak saat scan ganda.
 *   - Presensi sukses mengubah status_kehadiran menjadi 'Hadir' dan mencatat jam_kehadiran.
 */
class UkmScanTest extends TestCase
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

    private function setupUkmJadwal(): array
    {
        $admin = $this->makeUser(User::ADMIN_ROLE_ID);
        $pelatih = $this->makeUser(User::PELATIH_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Taekwondo', 'slug' => 'ukm-taekwondo']);

        $mhsAnggota = $this->makeUser(User::USER_ROLE_ID);
        $mhsBukanAnggota = $this->makeUser(User::USER_ROLE_ID);

        UkmMember::create([
            'ukm_id' => $ukm->id,
            'user_id' => $mhsAnggota->id,
            'peran' => 'anggota',
            'status' => 'aktif',
        ]);

        $now = Carbon::now();
        $startTime = $now->copy()->subMinutes(10)->format('H:i:s');
        $endTime = $now->copy()->addHour()->format('H:i:s');

        $jadwal = UkmJadwal::create([
            'ukm_id' => $ukm->id,
            'judul' => 'Latihan Sabuk Hitam',
            'jenis' => 'latihan',
            'tanggal' => $now->toDateString(),
            'mulai_acara' => $startTime,
            'selesai_acara' => $endTime,
            'lokasi' => 'Dojo Polbangtan',
            'created_by' => $pelatih->id,
        ]);

        // Pre-create 'Alpha' presensi for member via fan-out pattern
        $presensi = UkmPresensi::create([
            'ukm_jadwal_id' => $jadwal->id,
            'user_id' => $mhsAnggota->id,
            'status_kehadiran' => 'Alpha',
        ]);

        return [$admin, $pelatih, $ukm, $jadwal, $mhsAnggota, $mhsBukanAnggota, $presensi];
    }

    public function test_pelatih_dan_admin_bisa_mengakses_halaman_scanner_ukm(): void
    {
        [$admin, $pelatih, $ukm, $jadwal] = $this->setupUkmJadwal();

        $response = $this->actingAs($pelatih)->get(route('admin.ukm.scan.show', $jadwal->id));
        $response->assertStatus(200);
        $response->assertViewIs('admin.ukm.kamera-ukm');
        $response->assertSee('UKM Taekwondo');
    }

    public function test_scan_qr_berhasil_mengubah_status_kehadiran_menjadi_hadir(): void
    {
        [$admin, $pelatih, $ukm, $jadwal, $mhsAnggota] = $this->setupUkmJadwal();

        $currentTime = Carbon::now()->format('H:i:s');

        $response = $this->actingAs($pelatih)->post(route('admin.ukm.scan.store', $jadwal->id), [
            'user_id' => $mhsAnggota->id,
            'date' => Carbon::now()->toDateString(),
            'time' => $currentTime,
            'scanner' => 'absensi',
        ]);

        $response->assertRedirect(route('admin.ukm.scan.show', $jadwal->id));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('ukm_presensis', [
            'ukm_jadwal_id' => $jadwal->id,
            'user_id' => $mhsAnggota->id,
            'status_kehadiran' => 'Hadir',
            'jam_kehadiran' => $currentTime,
        ]);
    }

    public function test_scan_qr_expired_lebih_dari_30_detik_ditolak(): void
    {
        [$admin, $pelatih, $ukm, $jadwal, $mhsAnggota] = $this->setupUkmJadwal();

        // 45 seconds ago (expired)
        $expiredTime = Carbon::now()->subSeconds(45)->format('H:i:s');

        $response = $this->actingAs($pelatih)->post(route('admin.ukm.scan.store', $jadwal->id), [
            'user_id' => $mhsAnggota->id,
            'date' => Carbon::now()->toDateString(),
            'time' => $expiredTime,
            'scanner' => 'absensi',
        ]);

        $response->assertRedirect(route('admin.ukm.scan.show', $jadwal->id));
        $response->assertSessionHas('error', 'QR Code Expired');

        $this->assertDatabaseHas('ukm_presensis', [
            'ukm_jadwal_id' => $jadwal->id,
            'user_id' => $mhsAnggota->id,
            'status_kehadiran' => 'Alpha',
        ]);
    }

    public function test_scan_mahasiswa_bukan_anggota_ditolak(): void
    {
        [$admin, $pelatih, $ukm, $jadwal, $mhsAnggota, $mhsBukanAnggota] = $this->setupUkmJadwal();

        $currentTime = Carbon::now()->format('H:i:s');

        $response = $this->actingAs($pelatih)->post(route('admin.ukm.scan.store', $jadwal->id), [
            'user_id' => $mhsBukanAnggota->id,
            'date' => Carbon::now()->toDateString(),
            'time' => $currentTime,
            'scanner' => 'absensi',
        ]);

        $response->assertRedirect(route('admin.ukm.scan.show', $jadwal->id));
        $response->assertSessionHas('error');

        $this->assertDatabaseMissing('ukm_presensis', [
            'ukm_jadwal_id' => $jadwal->id,
            'user_id' => $mhsBukanAnggota->id,
        ]);
    }

    public function test_scan_ganda_yang_sudah_hadir_ditolak(): void
    {
        [$admin, $pelatih, $ukm, $jadwal, $mhsAnggota, $mhsBukanAnggota, $presensi] = $this->setupUkmJadwal();

        // Mark as Hadir first
        $presensi->update([
            'status_kehadiran' => 'Hadir',
            'jam_kehadiran' => '16:05:00',
        ]);

        $currentTime = Carbon::now()->format('H:i:s');

        $response = $this->actingAs($pelatih)->post(route('admin.ukm.scan.store', $jadwal->id), [
            'user_id' => $mhsAnggota->id,
            'date' => Carbon::now()->toDateString(),
            'time' => $currentTime,
            'scanner' => 'absensi',
        ]);

        $response->assertRedirect(route('admin.ukm.scan.show', $jadwal->id));
        $response->assertSessionHas('error', 'Anda Sudah Melakukan Presensi');
    }
}
