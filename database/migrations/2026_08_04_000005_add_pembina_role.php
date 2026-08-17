<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Menambah role 'pembina' (id=5) untuk Epic 01 — Modul UKM Dinamis, §7.1
 * docs/design/DESIGN-Epic01-Modul-UKM-Dinamis.md.
 *
 * SENGAJA berupa migrasi data (bukan RoleSeeder::class), karena RoleSeeder
 * sudah pernah dijalankan di produksi dan re-run seeder bukan bagian dari
 * alur deploy standar — sedangkan migrasi otomatis jalan setiap deploy.
 *
 * GUARD PRODUKSI: baris pembina hanya di-insert kalau ke-4 role lama
 * (admin/operator/user/pelatih) SUDAH ada — itu tandanya instalasi lama yang
 * auto-increment-nya sudah "aman" di 1-4, sehingga insert baris ke-5 akan
 * mendapat id=5 seperti yang diharapkan konstanta User::PEMBINA_ROLE_ID.
 *
 * Pada instalasi baru (fresh install / DB tes), tabel `roles` masih kosong
 * saat migrasi ini jalan — migrasi SELALU jalan sebelum seeder pada
 * `migrate:fresh --seed`, jadi insert di sini akan salah urutan (pembina
 * bisa dapat id=1). Guard di bawah membuat migrasi ini TIDAK melakukan
 * apa pun pada skenario itu; ordering yang benar untuk fresh install
 * diserahkan ke DatabaseSeeder.php (lihat comment di sana) — bukan ke
 * RoleSeeder.php, yang sengaja tidak disentuh sama sekali.
 */
return new class extends Migration
{
    private const LEGACY_ROLES = ['admin', 'operator', 'user', 'pelatih'];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $legacyRolesPresent = DB::table('roles')
            ->whereIn('name', self::LEGACY_ROLES)
            ->count() === count(self::LEGACY_ROLES);

        if (! $legacyRolesPresent) {
            // Fresh install / DB tes kosong — DatabaseSeeder yang menangani
            // urutan pembina=5 setelah RoleSeeder jalan.
            return;
        }

        DB::table('roles')->updateOrInsert(
            ['name' => 'pembina'],
            ['created_at' => now(), 'updated_at' => now()]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('roles')->where('name', 'pembina')->delete();
    }
};
