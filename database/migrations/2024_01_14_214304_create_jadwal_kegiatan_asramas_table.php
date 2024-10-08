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
        Schema::create('jadwal_kegiatan_asramas', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_kegiatan');
            $table->foreignId('blok_id')->nullable()->constrained('blok_ruangans')->onDelete('cascade');
            $table->enum('jenis_kegiatan', ['Apel','Upacara', 'Senam'])->nullable()->default(null);
            $table->time('mulai_acara')->nullable()->default(null);
            $table->time('selesai_acara')->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_kegiatan_asramas');
    }
};
