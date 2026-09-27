<?php

namespace Tests\Feature\Security;

use App\Imports\UsersImport;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ExcelUploadSecurityTest extends TestCase
{
    public function test_import_class_sistem_admin_rejects_file_exceeding_10mb(): void
    {
        $admin = User::where('role_id', User::ADMIN_ROLE_ID)->firstOrFail();

        // 11 MB file exceeds 10240 KB limit
        $largeFile = UploadedFile::fake()->create('users.xlsx', 11264, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $response = $this->actingAs($admin)->post(route('admin.sistemAdminImportClass'), [
            'file' => $largeFile,
        ]);

        $response->assertSessionHasErrors(['file']);
    }

    public function test_import_dosen_pa_rejects_file_exceeding_10mb(): void
    {
        $admin = User::where('role_id', User::ADMIN_ROLE_ID)->firstOrFail();

        $largeFile = UploadedFile::fake()->create('dosen_pa.xlsx', 11264, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $response = $this->actingAs($admin)->post(route('admin.dosen_pa.import'), [
            'file' => $largeFile,
        ]);

        $response->assertSessionHasErrors(['file']);
    }

    public function test_import_lulusan_rejects_file_exceeding_10mb(): void
    {
        $admin = User::where('role_id', User::ADMIN_ROLE_ID)->firstOrFail();

        $largeFile = UploadedFile::fake()->create('lulusan.xlsx', 11264, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $response = $this->actingAs($admin)->post(route('admin.lulusan.import'), [
            'file' => $largeFile,
        ]);

        $response->assertSessionHasErrors(['file']);
    }

    public function test_import_class_sistem_admin_does_not_leak_internal_exception(): void
    {
        $admin = User::where('role_id', User::ADMIN_ROLE_ID)->firstOrFail();

        // Corrupted content that causes parser exception
        $corruptFile = UploadedFile::fake()->createWithContent('test.xlsx', 'INVALID_BINARY_CONTENT');

        $response = $this->actingAs($admin)->post(route('admin.sistemAdminImportClass'), [
            'file' => $corruptFile,
        ]);

        $errorMsg = session('error');
        $this->assertNotNull($errorMsg, 'Session should have error key on failure');
        $this->assertStringNotContainsString('SQLSTATE', $errorMsg);
        $this->assertStringNotContainsString('Exception', $errorMsg);
        $this->assertStringNotContainsString('Stack trace', $errorMsg);
    }

    public function test_users_import_sanitizes_formula_injection(): void
    {
        $import = new UsersImport();
        $row = [
            0 => '12345678',
            1 => '=CMD|"/C calc"!A0', // formula injection attempt
            2 => 1,
            3 => 'test_formula@polbangtan.ac.id',
        ];

        $user = $import->model($row);

        $this->assertNotNull($user);
        $this->assertNotEquals('=CMD|"/C calc"!A0', $user->name);
        $this->assertTrue(
            str_starts_with($user->name, "'") || ! str_starts_with($user->name, '='),
            'Formula trigger character should be escaped with single quote or sanitized'
        );
    }

    public function test_laporan_izin_export_sanitizes_formula_injection(): void
    {
        $student = User::where('role_id', User::USER_ROLE_ID)->firstOrFail();
        $jenisIzin = \App\Models\JenisIzin::first() ?? \App\Models\JenisIzin::create([
            'nama' => 'Izin Test',
            'kode' => 'IT',
            'is_active' => true,
        ]);

        $pengajuan = \App\Models\PengajuanIzin::create([
            'user_id' => $student->id,
            'jenis_izin_id' => $jenisIzin->id,
            'keperluan' => '=cmd|\'/C calc\'!A0',
            'tujuan_lokasi' => '+1337-EXCEL-INJECTION',
            'waktu_berangkat' => Carbon::now()->addDay(),
            'waktu_kembali' => Carbon::now()->addDays(2),
            'nama_snapshot' => '@DANGEROUS_FORMULA',
            'nirm_snapshot' => $student->nim ?? '12345',
            'status' => 'disetujui',
            'nomor_surat' => 'AR.009/TEST/X/' . uniqid(),
            'qr_token' => 'test_token_' . uniqid(),
        ]);

        $export = new \App\Exports\LaporanIzinExport(collect([$pengajuan]));
        $html = $export->view()->render();

        $this->assertStringNotContainsString('<td>+1337-EXCEL-INJECTION</td>', $html);
        $this->assertStringNotContainsString('<td>@DANGEROUS_FORMULA</td>', $html);
        $this->assertStringContainsString("'+1337-EXCEL-INJECTION", $html);
        $this->assertStringContainsString("'@DANGEROUS_FORMULA", $html);
    }
}
