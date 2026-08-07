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
        Schema::create('izin_workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_izin_id')->constrained('jenis_izins')->cascadeOnDelete();
            $table->unsignedTinyInteger('urutan');
            $table->string('label');
            $table->string('blok')->nullable();
            $table->enum('resolver', ['pejabat', 'dosen_pa', 'pembina_ukm', 'petugas_jaga']);
            $table->json('jabatan')->nullable();
            $table->enum('lingkup', ['global', 'prodi', 'blok'])->default('global');
            $table->enum('mode', ['any', 'all'])->default('any');
            $table->enum('kondisi', ['selalu', 'jika_ada_ukm', 'jika_bermalam'])->default('selalu');
            $table->enum('resolve_saat', ['submit', 'langkah_aktif'])->default('submit');
            $table->enum('fallback_resolver', ['pejabat', 'dosen_pa', 'pembina_ukm', 'petugas_jaga'])->nullable();
            $table->json('fallback_jabatan')->nullable();
            $table->unsignedSmallInteger('sla_jam')->default(24);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['jenis_izin_id', 'urutan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('izin_workflow_steps');
    }
};
