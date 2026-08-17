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
        Schema::create('pengajuan_izins', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat')->unique()->nullable();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('jenis_izin_id')->constrained('jenis_izins');
            $table->foreignId('ukm_id')->nullable()->constrained('ukms')->nullOnDelete();

            $table->text('keperluan');
            $table->string('tujuan_lokasi');
            $table->text('alamat_tujuan')->nullable();
            $table->dateTime('waktu_berangkat');
            $table->dateTime('waktu_kembali');

            // snapshot identitas
            $table->string('nama_snapshot');
            $table->string('nirm_snapshot')->nullable();
            $table->string('kelas_snapshot')->nullable();
            $table->string('prodi_snapshot')->nullable();
            $table->string('no_kamar_snapshot')->nullable();
            $table->string('no_hp_snapshot')->nullable();

            $table->enum('status', [
                'draft', 'diajukan', 'disetujui', 'ditolak', 'dibatalkan',
                'berjalan', 'selesai', 'terlambat', 'kadaluarsa',
            ])->default('draft');
            $table->unsignedTinyInteger('langkah_aktif')->nullable();
            $table->timestamp('diajukan_at')->nullable();
            $table->timestamp('disetujui_at')->nullable();
            $table->text('alasan_penolakan')->nullable();

            $table->char('qr_token', 40)->unique()->nullable();
            $table->timestamp('keluar_at')->nullable();
            $table->timestamp('kembali_at')->nullable();

            // konfirmasi tiba (delegasi/IB)
            $table->string('tiba_di')->nullable();
            $table->timestamp('tiba_at')->nullable();
            $table->string('tiba_dikonfirmasi_oleh')->nullable();
            $table->string('tiba_kontak')->nullable();
            $table->string('tiba_bukti_path')->nullable();

            $table->foreignId('pelanggaran_id')->nullable()->constrained('pelanggarans')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'waktu_kembali']);
            $table->index(['status', 'waktu_berangkat']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_izins');
    }
};
