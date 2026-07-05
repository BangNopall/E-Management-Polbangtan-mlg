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
        Schema::create('presensi_upacaras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwalKegiatanAsrama_id')->nullable()->constrained('jadwal_kegiatan_asramas')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->time('jam_kehadiran')->nullable()->default(null);
            $table->enum('status_kehadiran', ['Hadir','Alpha','Izin'])->nullable()->default('Alpha');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensi_upacaras');
    }
};
