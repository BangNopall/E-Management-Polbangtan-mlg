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
        Schema::create('ukm_jadwals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ukm_id')->constrained('ukms')->cascadeOnDelete();
            $table->string('judul');
            $table->enum('jenis', ['latihan', 'kegiatan_wajib'])->default('latihan');
            $table->date('tanggal');
            $table->time('mulai_acara');
            $table->time('selesai_acara');
            $table->string('lokasi')->nullable();
            // Epic 1.4 — verifikasi pembina
            $table->enum('status_verifikasi', ['draft', 'menunggu', 'disetujui', 'ditolak'])->default('draft');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('catatan_pembina')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['ukm_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ukm_jadwals');
    }
};
