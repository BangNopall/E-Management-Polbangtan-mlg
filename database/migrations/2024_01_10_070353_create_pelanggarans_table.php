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
        Schema::create('pelanggarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('jenis_pelanggaran_id')->nullable()->constrained('jenis_pelanggarans')->onDelete('cascade');
            $table->date('date')->nullable();
            $table->time('time')->nullable();
            $table->enum('statusPelanggaran', ['scanned','submitted', 'rejected', 'progressing', 'Done'])->nullable()->default('scanned');
            $table->string('Hukuman')->nullable();
            $table->string('rejected_message')->nullable();
            $table->foreignId('accepted_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelanggarans');
    }
};
