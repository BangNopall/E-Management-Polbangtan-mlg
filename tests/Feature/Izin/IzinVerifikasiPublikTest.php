<?php

namespace Tests\Feature\Izin;

use App\Models\BlokRuangan;
use App\Models\IzinApproval;
use App\Models\IzinWorkflowStep;
use App\Models\JenisIzin;
use App\Models\Kelas;
use App\Models\LevelKelas;
use App\Models\Pejabat;
use App\Models\PengajuanIzin;
use App\Models\Prodi;
use App\Models\User;
use App\Services\Izin\NomorSuratService;
use App\Services\Izin\PengajuanIzinService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class IzinVerifikasiPublikTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;
    protected User $dosenPa;
    protected JenisIzin $jenisIzin;

    protected function setUp(): void
    {
        parent::setUp();

        $prodi = Prodi::create(['prodi' => 'Teknologi Pertanian']);
        $this->dosenPa = User::factory()->create(['role_id' => 1, 'name' => 'Dosen PA User']);
        $levelKelas = LevelKelas::create(['nama_level_kelas' => 'Tingkat 1']);
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

        $this->jenisIzin = JenisIzin::create([
            'kode' => 'IZIN_VERIF_' . rand(1000, 9999),
            'nama' => 'Izin Verifikasi Test',
            'butuh_ukm' => false,
            'min_ajukan_jam' => 0,
            'maks_durasi_jam' => 24,
            'is_active' => true,
        ]);

        IzinWorkflowStep::create([
            'jenis_izin_id' => $this->jenisIzin->id,
            'urutan' => 1,
            'label' => 'Persetujuan Dosen PA',
            'resolver' => 'dosen_pa',
            'mode' => 'any',
            'resolve_saat' => 'submit',
        ]);
    }

    public function test_penomoran_surat_paralel_tetap_unik_dan_berurutan(): void
    {
        $nomorSuratService = app(NomorSuratService::class);
        $date = Carbon::parse('2026-08-07');
        $generated = [];

        // Generate 50 nomor surat secara berurutan
        for ($i = 0; $i < 50; $i++) {
            $pengajuan = PengajuanIzin::create([
                'user_id' => $this->student->id,
                'jenis_izin_id' => $this->jenisIzin->id,
                'keperluan' => 'Penerbitan Surat ' . $i,
                'tujuan_lokasi' => 'Malang',
                'waktu_berangkat' => now(),
                'waktu_kembali' => now()->addHours(5),
                'nama_snapshot' => $this->student->name,
                'status' => 'disetujui',
                'disetujui_at' => $date,
                'nomor_surat' => $nomorSuratService->generateNext($date),
            ]);

            $generated[] = $pengajuan->nomor_surat;
        }

        // Pastikan total nomor unik persis 50
        $this->assertCount(50, array_unique($generated));
        $this->assertEquals('AR.009/001/VIII/2026', $generated[0]);
        $this->assertEquals('AR.009/050/VIII/2026', $generated[49]);
    }

    public function test_verifikasi_publik_dengan_signed_url_valid_returns_200(): void
    {
        $service = app(PengajuanIzinService::class);
        $pengajuan = $service->ajukan($this->student, [
            'jenis_izin_id' => $this->jenisIzin->id,
            'keperluan' => 'Izin keluar kampus',
            'tujuan_lokasi' => 'Batu',
            'waktu_berangkat' => now()->addHours(2),
            'waktu_kembali' => now()->addHours(6),
        ]);

        // Approve langkah 1
        $approval = $pengajuan->approvals->first();
        $service->setujui($approval, $this->dosenPa);
        $pengajuan->refresh();

        // Buat Signed URL resmi
        $signedUrl = URL::signedRoute('publik.verifikasi.izin', ['qr_token' => $pengajuan->qr_token]);

        $response = $this->get($signedUrl);
        $response->assertStatus(200);
        $response->assertSee('Surat Izin Resmi');
        $response->assertSee($pengajuan->nomor_surat);
        $response->assertSee($this->student->name);
    }

    public function test_verifikasi_publik_tanpa_signature_valid_returns_403(): void
    {
        $service = app(PengajuanIzinService::class);
        $pengajuan = $service->ajukan($this->student, [
            'jenis_izin_id' => $this->jenisIzin->id,
            'keperluan' => 'Izin keluar',
            'tujuan_lokasi' => 'Batu',
            'waktu_berangkat' => now()->addHours(2),
            'waktu_kembali' => now()->addHours(6),
        ]);

        $approval = $pengajuan->approvals->first();
        $service->setujui($approval, $this->dosenPa);
        $pengajuan->refresh();

        // Mengakses route publik TANPA signature parameter
        $rawUrl = route('publik.verifikasi.izin', ['qr_token' => $pengajuan->qr_token]);

        $response = $this->get($rawUrl);
        $response->assertStatus(403);
    }

    public function test_verifikasi_publik_dengan_token_acak_returns_404(): void
    {
        $fakeToken = 'invalid-qr-token-123456789';
        $signedUrl = URL::signedRoute('publik.verifikasi.izin', ['qr_token' => $fakeToken]);

        $response = $this->get($signedUrl);
        $response->assertStatus(404);
    }

    public function test_halaman_verifikasi_publik_tidak_menampilkan_data_sensitif_privasi(): void
    {
        $service = app(PengajuanIzinService::class);
        $pengajuan = $service->ajukan($this->student, [
            'jenis_izin_id' => $this->jenisIzin->id,
            'keperluan' => 'Alasan Rahasia Pribadi',
            'tujuan_lokasi' => 'Surabaya',
            'waktu_berangkat' => now()->addHours(2),
            'waktu_kembali' => now()->addHours(6),
        ]);

        $approval = $pengajuan->approvals->first();
        $service->setujui($approval, $this->dosenPa);
        $pengajuan->refresh();

        $signedUrl = URL::signedRoute('publik.verifikasi.izin', ['qr_token' => $pengajuan->qr_token]);

        $response = $this->get($signedUrl);
        $response->assertStatus(200);

        // Strict Privacy Assertions (§7 Spec)
        $response->assertDontSee('Alasan Rahasia Pribadi'); // Keperluan dilarang tampil
        $response->assertDontSee('08123456789'); // No HP dilarang tampil
    }

    public function test_download_pdf_surat_izin_berhasil_untuk_izin_disetujui(): void
    {
        $service = app(PengajuanIzinService::class);
        $pengajuan = $service->ajukan($this->student, [
            'jenis_izin_id' => $this->jenisIzin->id,
            'keperluan' => 'Undangan Seminar',
            'tujuan_lokasi' => 'Malang',
            'waktu_berangkat' => now()->addHours(2),
            'waktu_kembali' => now()->addHours(6),
        ]);

        $approval = $pengajuan->approvals->first();
        $service->setujui($approval, $this->dosenPa);
        $pengajuan->refresh();

        $response = $this->actingAs($this->student)->get(route('home.izin.pdf', $pengajuan->id));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }
}
