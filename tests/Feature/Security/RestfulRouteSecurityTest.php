<?php

namespace Tests\Feature\Security;

use App\Models\KategoriPelanggaran;
use App\Models\User;
use Tests\TestCase;

class RestfulRouteSecurityTest extends TestCase
{
    public function test_hapus_kategori_pelanggaran_menolak_metode_get(): void
    {
        $admin = User::where('role_id', User::ADMIN_ROLE_ID)->firstOrFail();
        $kategori = KategoriPelanggaran::first() ?? KategoriPelanggaran::create(['name' => 'Kategori Uji']);

        // GET request on destructive action should be rejected with 405 Method Not Allowed
        $response = $this->actingAs($admin)->get("/deletekategori/{$kategori->id}");

        $response->assertStatus(405);
    }

    public function test_hapus_kategori_pelanggaran_berhasil_dengan_metode_delete(): void
    {
        $admin = User::where('role_id', User::ADMIN_ROLE_ID)->firstOrFail();
        $kategori = KategoriPelanggaran::create(['name' => 'Kategori Hapus Test']);

        $response = $this->actingAs($admin)->delete("/deletekategori/{$kategori->id}");

        $response->assertStatus(302);
        $this->assertDatabaseMissing('kategori_pelanggarans', ['id' => $kategori->id]);
    }

    public function test_route_delete_piket_petugas_single_terdaftar(): void
    {
        $this->assertTrue(
            \Illuminate\Support\Facades\Route::has('admin.deletePiketPetugasSingle'),
            'Route admin.deletePiketPetugasSingle must be registered'
        );
    }
}
