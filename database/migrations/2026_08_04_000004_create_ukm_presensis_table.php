<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ukm_presensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ukm_jadwal_id')->constrained('ukm_jadwals')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status_kehadiran', ['Hadir', 'Izin', 'Alpha'])->default('Alpha');
            $table->time('jam_kehadiran')->nullable();
            $table->timestamps();

            $table->unique(['ukm_jadwal_id', 'user_id']); // cegah presensi ganda
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ukm_presensis');
    }
};
