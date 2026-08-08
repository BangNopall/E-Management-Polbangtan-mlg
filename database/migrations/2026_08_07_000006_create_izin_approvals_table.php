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
        Schema::create('izin_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_izin_id')->constrained('pengajuan_izins')->cascadeOnDelete();
            $table->unsignedTinyInteger('urutan');
            $table->string('label_snapshot');
            $table->string('blok_snapshot')->nullable();
            $table->foreignId('approver_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('approver_nama_snapshot')->nullable();
            $table->enum('mode', ['any', 'all'])->default('any');
            $table->enum('status', ['menunggu', 'disetujui', 'ditolak', 'dilewati'])->default('menunggu');
            $table->text('catatan')->nullable();
            $table->timestamp('dibuka_at')->nullable();
            $table->timestamp('acted_at')->nullable();
            $table->foreignId('acted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('acted_ip', 45)->nullable();
            $table->string('acted_user_agent')->nullable();
            $table->timestamps();

            $table->unique(['pengajuan_izin_id', 'urutan']);
            $table->index(['approver_user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('izin_approvals');
    }
};
