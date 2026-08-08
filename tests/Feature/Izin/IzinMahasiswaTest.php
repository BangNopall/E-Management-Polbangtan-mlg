<?php

namespace Tests\Feature\Izin;

use App\Models\BlokRuangan;
use App\Models\IzinWorkflowStep;
use App\Models\JenisIzin;
use App\Models\Kelas;
use App\Models\Pejabat;
use App\Models\PengajuanIzin;
use App\Models\Prodi;
use App\Models\Ukm;
use App\Models\UkmMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IzinMahasiswaTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;
    protected User $dosenPa;
    protected User $kaprodi;
    protected JenisIzin $jenisBiasa;
    protected JenisIzin $jenisUkm;
    protected Ukm $ukm;

    protected function setUp(): void
    {
        parent::setUp();

        $prodi = Prodi::create(['prodi' => 'Teknologi Pertanian']);
        $this->dosenPa = User::factory()->create(['role_id' => 3, 'name' => 'Dosen PA']);
        $levelKelas = \App\Models\LevelKelas::create(['nama_level_kelas' => 'Tingkat 1']);
        $kelas = Kelas::create(['kelas' => 'TP-1A', 'nama_kelas' => 'TP-1A', 'prodi_id' => $prodi->id, 'dosen_pa_id' => $this->dosenPa->id, 'level_kelas_id' => $levelKelas->id]);
        $blok = BlokRuangan::create(['name' => 'Blok A']);

        $this->student = User::factory()->create([
            'role_id' => 3,
            'prodi_id' => $prodi->id,
            'kelas_id' => $kelas->id,
            'blok_ruangan_id' => $blok->id,
            'nim' => '123456',
            'no_hp' => '08123456789',
        ]);

        $this->kaprodi = User::factory()->create(['role_id' => 1, 'name' => 'Kaprodi User']);
        Pejabat::create([
            'jabatan' => 'kaprodi',
            'user_id' => $this->kaprodi->id,
            'lingkup' => 'global',
            'is_active' => true,
        ]);

        // Jenis Izin Keluar (Biasa)
        $this->jenisBiasa = JenisIzin::create([
            'kode' => 'IZIN_KELUAR_' . rand(1000, 9999),
            'nama' => 'Izin Keluar Asrama',
            'butuh_ukm' => false,
            'min_ajukan_jam' => 2,
            'maks_durasi_jam' => 12,
            'is_active' => true,
        ]);

        IzinWorkflowStep::create([
            'jenis_izin_id' => $this->jenisBiasa->id,
            'urutan' => 1,
            'label' => 'Persetujuan Dosen PA',
            'resolver' => 'dosen_pa',
            'mode' => 'any',
            'resolve_saat' => 'submit',
        ]);

        IzinWorkflowStep::create([
            'jenis_izin_id' => $this->jenisBiasa->id,
            'urutan' => 2,
            'label' => 'Persetujuan Kaprodi',
            'resolver' => 'pejabat',
            'jabatan' => ['kaprodi'],
            'mode' => 'any',
            'resolve_saat' => 'submit',
        ]);

        // Jenis Izin UKM
        $this->jenisUkm = JenisIzin::create([
            'kode' => 'DELEGASI_' . rand(1000, 9999),
            'nama' => 'Izin Delegasi UKM',
            'butuh_ukm' => true,
            'min_ajukan_jam' => 0,
            'maks_durasi_jam' => 72,
            'is_active' => true,
        ]);

        $this->ukm = Ukm::create(['nama' => 'UKM Olahraga', 'slug' => 'ukm-olahraga', 'deskripsi' => 'Futsal & Basket']);
        $pembinaUser = User::factory()->create(['role_id' => 5, 'name' => 'Pembina UKM']);

        UkmMember::create([
            'ukm_id' => $this->ukm->id,
            'user_id' => $this->student->id,
            'peran' => 'anggota',
            'status' => 'aktif',
        ]);

        UkmMember::create([
            'ukm_id' => $this->ukm->id,
            'user_id' => $pembinaUser->id,
            'peran' => 'pembina',
            'status' => 'aktif',
        ]);

        IzinWorkflowStep::create([
            'jenis_izin_id' => $this->jenisUkm->id,
            'urutan' => 1,
            'label' => 'Persetujuan Pembina UKM',
            'resolver' => 'pembina_ukm',
            'mode' => 'any',
            'resolve_saat' => 'submit',
        ]);
    }

    public function test_mahasiswa_dapat_melihat_halaman_index_dan_create_izin(): void
    {
        $response = $this->actingAs($this->student)->get(route('home.izin.index'));
        $response->assertStatus(200);

        $responseCreate = $this->actingAs($this->student)->get(route('home.izin.create'));
        $responseCreate->assertStatus(200);
    }

    public function test_pengajuan_izin_sukses_dan_membentuk_approvals_chain(): void
    {
        $payload = [
            'jenis_izin_id' => $this->jenisBiasa->id,
            'keperluan' => 'Beli buku di toko buku',
            'tujuan_lokasi' => 'Malang Kota',
            'waktu_berangkat' => now()->addHours(3)->toDateTimeString(),
            'waktu_kembali' => now()->addHours(8)->toDateTimeString(),
        ];

        $response = $this->actingAs($this->student)->post(route('home.izin.store'), $payload);

        $this->assertDatabaseHas('pengajuan_izins', [
            'user_id' => $this->student->id,
            'jenis_izin_id' => $this->jenisBiasa->id,
            'keperluan' => 'Beli buku di toko buku',
            'status' => 'diajukan',
            'langkah_aktif' => 1,
        ]);

        $pengajuan = PengajuanIzin::where('user_id', $this->student->id)->first();
        $response->assertRedirect(route('home.izin.show', $pengajuan->id));

        $this->assertCount(2, $pengajuan->approvals);
        $this->assertEquals('menunggu', $pengajuan->approvals->first()->status);
        $this->assertEquals($this->dosenPa->id, $pengajuan->approvals->first()->approver_user_id);
    }

    public function test_gerbang_1_profil_incomplete_ditolak(): void
    {
        $incompleteStudent = User::factory()->create(['role_id' => 3, 'prodi_id' => null]);

        $payload = [
            'jenis_izin_id' => $this->jenisBiasa->id,
            'keperluan' => 'Belanja',
            'tujuan_lokasi' => 'Pasar',
            'waktu_berangkat' => now()->addHours(4)->toDateTimeString(),
            'waktu_kembali' => now()->addHours(6)->toDateTimeString(),
        ];

        $response = $this->actingAs($incompleteStudent)->post(route('home.izin.store'), $payload);
        $response->assertSessionHasErrors('profil');
    }

    public function test_gerbang_2_izin_aktif_exist_ditolak(): void
    {
        PengajuanIzin::create([
            'user_id' => $this->student->id,
            'jenis_izin_id' => $this->jenisBiasa->id,
            'keperluan' => 'Izin Pertama',
            'tujuan_lokasi' => 'Malang',
            'waktu_berangkat' => now()->addHours(3),
            'waktu_kembali' => now()->addHours(8),
            'nama_snapshot' => $this->student->name,
            'status' => 'diajukan',
        ]);

        $payload = [
            'jenis_izin_id' => $this->jenisBiasa->id,
            'keperluan' => 'Izin Kedua',
            'tujuan_lokasi' => 'Batu',
            'waktu_berangkat' => now()->addHours(4)->toDateTimeString(),
            'waktu_kembali' => now()->addHours(6)->toDateTimeString(),
        ];

        $response = $this->actingAs($this->student)->post(route('home.izin.store'), $payload);
        $response->assertSessionHasErrors('status');
    }

    public function test_gerbang_4_syarat_ukm_ditolak_jika_tidak_memilih_ukm(): void
    {
        $payload = [
            'jenis_izin_id' => $this->jenisUkm->id,
            'keperluan' => 'Lomba Futsal',
            'tujuan_lokasi' => 'Surabaya',
            'waktu_berangkat' => now()->addHours(2)->toDateTimeString(),
            'waktu_kembali' => now()->addHours(10)->toDateTimeString(),
        ];

        $response = $this->actingAs($this->student)->post(route('home.izin.store'), $payload);
        $response->assertSessionHasErrors('ukm_id');
    }

    public function test_gerbang_5_lead_time_minimum_ditolak(): void
    {
        $payload = [
            'jenis_izin_id' => $this->jenisBiasa->id,
            'keperluan' => 'Darurat',
            'tujuan_lokasi' => 'Klinik',
            'waktu_berangkat' => now()->addMinutes(30)->toDateTimeString(), // min lead time is 2 hours
            'waktu_kembali' => now()->addHours(5)->toDateTimeString(),
        ];

        $response = $this->actingAs($this->student)->post(route('home.izin.store'), $payload);
        $response->assertSessionHasErrors('waktu_berangkat');
    }

    public function test_gerbang_6_maksimal_durasi_ditolak(): void
    {
        $payload = [
            'jenis_izin_id' => $this->jenisBiasa->id,
            'keperluan' => 'Liburan Panjang',
            'tujuan_lokasi' => 'Bali',
            'waktu_berangkat' => now()->addHours(3)->toDateTimeString(),
            'waktu_kembali' => now()->addHours(24)->toDateTimeString(), // max duration is 12 hours
        ];

        $response = $this->actingAs($this->student)->post(route('home.izin.store'), $payload);
        $response->assertSessionHasErrors('waktu_kembali');
    }

    public function test_idor_mahasiswa_lain_tidak_dapat_melihat_atau_membatalkan_izin(): void
    {
        $otherStudent = User::factory()->create(['role_id' => 3]);

        $pengajuan = PengajuanIzin::create([
            'user_id' => $this->student->id,
            'jenis_izin_id' => $this->jenisBiasa->id,
            'keperluan' => 'Izin Rahasia',
            'tujuan_lokasi' => 'Batu',
            'waktu_berangkat' => now()->addHours(3),
            'waktu_kembali' => now()->addHours(8),
            'nama_snapshot' => $this->student->name,
            'status' => 'diajukan',
        ]);

        $responseShow = $this->actingAs($otherStudent)->get(route('home.izin.show', $pengajuan->id));
        $responseShow->assertStatus(403);

        $responseBatal = $this->actingAs($otherStudent)->post(route('home.izin.batal', $pengajuan->id));
        $responseBatal->assertStatus(403);
    }

    public function test_mahasiswa_dapat_membatalkan_pengajuan_sendiri(): void
    {
        $pengajuan = PengajuanIzin::create([
            'user_id' => $this->student->id,
            'jenis_izin_id' => $this->jenisBiasa->id,
            'keperluan' => 'Pembatalan Izin',
            'tujuan_lokasi' => 'Malang',
            'waktu_berangkat' => now()->addHours(3),
            'waktu_kembali' => now()->addHours(8),
            'nama_snapshot' => $this->student->name,
            'status' => 'diajukan',
        ]);

        $response = $this->actingAs($this->student)->post(route('home.izin.batal', $pengajuan->id));
        $response->assertRedirect(route('home.izin.show', $pengajuan->id));

        $this->assertDatabaseHas('pengajuan_izins', [
            'id' => $pengajuan->id,
            'status' => 'dibatalkan',
        ]);
    }

    public function test_pengajuan_izin_sukses_meskipun_kelas_belum_memiliki_dosen_pa(): void
    {
        $operator = User::factory()->create(['role_id' => \App\Models\User::OPERATOR_ROLE_ID, 'name' => 'Operator Staff']);

        $this->student->kelas->update(['dosen_pa_id' => null]);

        $payload = [
            'jenis_izin_id' => $this->jenisBiasa->id,
            'keperluan' => 'Izin ke apotek tanpa Dosen PA',
            'tujuan_lokasi' => 'Apotek K-24',
            'waktu_berangkat' => now()->addHours(3)->toDateTimeString(),
            'waktu_kembali' => now()->addHours(6)->toDateTimeString(),
        ];

        $response = $this->actingAs($this->student)->post(route('home.izin.store'), $payload);

        $pengajuan = PengajuanIzin::where('user_id', $this->student->id)->where('keperluan', 'Izin ke apotek tanpa Dosen PA')->first();
        $this->assertNotNull($pengajuan);
        $response->assertRedirect(route('home.izin.show', $pengajuan->id));

        $this->assertEquals($operator->id, $pengajuan->approvals->first()->approver_user_id);
    }
}
