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
        Schema::create('jenis_izins', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->string('kode_form')->nullable();
            $table->boolean('butuh_bermalam')->default(false);
            $table->boolean('butuh_ukm')->default(false);
            $table->boolean('butuh_konfirmasi_tiba')->default(false);
            $table->unsignedSmallInteger('maks_durasi_jam')->nullable();
            $table->unsignedSmallInteger('min_ajukan_jam')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_izins');
    }
};
