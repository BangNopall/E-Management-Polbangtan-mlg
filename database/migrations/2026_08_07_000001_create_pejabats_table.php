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
        Schema::create('pejabats', function (Blueprint $table) {
            $table->id();
            $table->enum('jabatan', ['kaprodi', 'kepala_asrama', 'unit_kemahasiswaan', 'wadir_kemahasiswaan']);
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('lingkup', ['global', 'prodi', 'blok'])->default('global');
            $table->unsignedBigInteger('lingkup_id')->nullable();   // prodi_id / blok_ruangan_id
            $table->date('mulai_menjabat')->nullable();
            $table->date('selesai_menjabat')->nullable();           // null = masih menjabat
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['jabatan', 'lingkup', 'lingkup_id', 'is_active'], 'pejabats_resolver_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pejabats');
    }
};
