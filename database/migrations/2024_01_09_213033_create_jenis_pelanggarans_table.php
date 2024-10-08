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
        Schema::create('jenis_pelanggarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_id')->nullable()->constrained('kategori_pelanggarans')->onDelete('cascade');
            $table->string('jenis_pelanggaran');
            $table->integer('poin');
            $table->enum('sub_kategori', ['Ringan', 'Sedang', 'Berat'])->default('Ringan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_pelanggarans');
    }
};
