<?php

namespace Tests\Feature\Security;

use App\Models\JenisIzin;
use App\Models\PengajuanIzin;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class DomPdfHardeningTest extends TestCase
{
    public function test_dompdf_configuration_disables_remote_access_and_javascript(): void
    {
        $dompdfConfig = config('dompdf');
        $enableRemote = $dompdfConfig['defines']['DOMPDF_ENABLE_REMOTE'] ?? $dompdfConfig['options']['enable_remote'] ?? $dompdfConfig['enable_remote'] ?? true;
        $enableJs = $dompdfConfig['defines']['DOMPDF_ENABLE_JAVASCRIPT'] ?? $dompdfConfig['options']['enable_javascript'] ?? $dompdfConfig['enable_javascript'] ?? true;

        $this->assertFalse((bool) $enableRemote, 'DOMPDF enable_remote must be false to prevent SSRF');
        $this->assertFalse((bool) $enableJs, 'DOMPDF enable_javascript must be false to prevent XSS');
    }

    public function test_pejabat_dan_dosen_pa_dapat_mengunduh_pdf_surat_izin(): void
    {
        $pejabat = User::where('role_id', User::PEJABAT_ROLE_ID)->first();
        if (!$pejabat) {
            $pejabat = User::create([
                'name' => 'Pejabat Test',
                'email' => 'pejabat_test@polbangtan.ac.id',
                'password' => bcrypt('password'),
                'role_id' => User::PEJABAT_ROLE_ID,
                'status' => 'didalam',
            ]);
        }

        $student = User::where('role_id', User::USER_ROLE_ID)->firstOrFail();
        $jenisIzin = JenisIzin::first() ?? JenisIzin::create([
            'nama' => 'Izin Dinas',
            'kode' => 'ID',
            'is_active' => true,
        ]);

        $pengajuan = PengajuanIzin::create([
            'user_id' => $student->id,
            'jenis_izin_id' => $jenisIzin->id,
            'keperluan' => 'Keperluan Dinas',
            'tujuan_lokasi' => 'Surabaya',
            'waktu_berangkat' => Carbon::now()->addDay(),
            'waktu_kembali' => Carbon::now()->addDays(2),
            'nama_snapshot' => $student->name,
            'nirm_snapshot' => $student->nim ?? '12345',
            'status' => 'disetujui',
            'nomor_surat' => rand(100, 99999) . '/21/09/2026',
            'qr_token' => 'test_token_' . uniqid(),
        ]);

        $response = $this->actingAs($pejabat)->get(route('admin.izin.persetujuan.pdf', $pengajuan->id));

        // Pejabat should NOT receive 403 Forbidden!
        $this->assertNotEquals(403, $response->getStatusCode(), 'Pejabat must not be blocked with 403 Forbidden when accessing approved permit PDF');
    }

    public function test_generate_surat_izin_pdf_job_disables_remote(): void
    {
        $reflection = new \ReflectionClass(\App\Jobs\GenerateSuratIzinPdfJob::class);
        $fileContents = file_get_contents($reflection->getFileName());

        $this->assertStringNotContainsString("'isRemoteEnabled' => true", $fileContents, 'GenerateSuratIzinPdfJob must not enable remote resources.');
    }
}
