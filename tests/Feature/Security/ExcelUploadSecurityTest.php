<?php

namespace Tests\Feature\Security;

use App\Imports\UsersImport;
use App\Models\User;
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
}
