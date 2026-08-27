<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('roles')->insert([
            ['name' => 'pelatih_ukm', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'security', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'dosen_pa', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'pejabat', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('roles')->whereIn('name', ['pelatih_ukm', 'security', 'dosen_pa', 'pejabat'])->delete();
    }
};
