<?php

namespace Tests\Feature\Infrastructure;

use App\Jobs\GenerateSuratIzinPdfJob;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Infrastructure, Docker & Production Readiness Verification Tests
 *
 * Menguji kesiapan environment runtime kontainer, hak akses storage,
 * keabsahan endpoint healthcheck, pendaftaran cron scheduler,
 * serta proteksi berkas konfigurasi Nginx dan Docker.
 */
class DockerHealthAndSecurityTest extends TestCase
{
    /**
     * Memastikan healthcheck endpoint bawaan Laravel 13 (/up) merespon 200 OK
     * untuk digunakan oleh docker compose healthcheck dan reverse proxy.
     */
    public function test_healthcheck_endpoint_returns_ok(): void
    {
        $response = $this->get('/up');

        $response->assertStatus(200);
    }

    /**
     * Memastikan seluruh direktori storage krusial dapat ditulis oleh proses PHP-FPM
     * (termasuk storage font DomPDF dan direktori privat surat izin).
     */
    public function test_required_storage_directories_exist_and_are_writable(): void
    {
        $paths = [
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('logs'),
            storage_path('fonts'),
            storage_path('app/private/izins'),
        ];

        foreach ($paths as $path) {
            if (!File::exists($path)) {
                File::makeDirectory($path, 0775, true, true);
            }

            $this->assertDirectoryExists($path, "Direktori {$path} harus ada.");
            $this->assertTrue(is_writable($path), "Direktori {$path} harus writable oleh proses aplikasi.");

            // Uji penulisan file sementara dan penghapusan
            $tempFile = $path . '/.write_test_' . uniqid();
            $written = @file_put_contents($tempFile, 'healthcheck');
            $this->assertNotFalse($written, "Gagal menulis file verifikasi di {$path}");
            @unlink($tempFile);
        }
    }

    /**
     * Memastikan berkas surat izin privat disimpan di storage lokal privat
     * dan tidak berada di bawah direktori publik.
     */
    public function test_private_izin_documents_path_security(): void
    {
        $privatePath = storage_path('app/private/izins');
        $publicPath = public_path();

        $this->assertStringStartsNotWith($publicPath, $privatePath, 'Direktori privat izin tidak boleh berada di dalam public root.');
    }

    /**
     * Memastikan semua scheduled artisan commands terdaftar di Console Application.
     */
    public function test_scheduled_artisan_commands_are_registered(): void
    {
        $allCommands = array_keys(Artisan::all());

        $this->assertContains('izin:tandai-kadaluarsa', $allCommands, 'Perintah izin:tandai-kadaluarsa harus terdaftar.');
        $this->assertContains('izin:periksa-keterlambatan', $allCommands, 'Perintah izin:periksa-keterlambatan harus terdaftar.');
        $this->assertContains('izin:ingatkan-approver', $allCommands, 'Perintah izin:ingatkan-approver harus terdaftar.');
    }

    /**
     * Memastikan Queue Job pembuat PDF dapat di-push ke antrian secara valid.
     */
    public function test_queue_job_can_be_dispatched(): void
    {
        Queue::fake();

        // Dispatch dummy instantiation test with model instance
        $pengajuan = new \App\Models\PengajuanIzin();
        $job = new GenerateSuratIzinPdfJob($pengajuan);
        dispatch($job);

        Queue::assertPushed(GenerateSuratIzinPdfJob::class);
    }

    /**
     * Memverifikasi aturan keamanan file sensitif pada konfigurasi Nginx Production.
     */
    public function test_nginx_production_config_contains_hardening_rules(): void
    {
        $nginxConfPath = base_path('nginx/production.conf');

        if (!File::exists($nginxConfPath)) {
            $nginxConfPath = base_path('nginx/default.conf');
        }

        $this->assertFileExists($nginxConfPath);
        $content = File::get($nginxConfPath);

        // Memastikan ada blokade file tersembunyi (.env, .git)
        $this->assertStringContainsString('location ~ /\\.(?!well-known).*', $content);
        $this->assertStringContainsString('deny all', $content);

        // Memastikan ada header keamanan esensial
        $this->assertStringContainsString('X-Frame-Options', $content);
        $this->assertStringContainsString('X-Content-Type-Options', $content);
        $this->assertStringContainsString('client_max_body_size', $content);
    }
}
