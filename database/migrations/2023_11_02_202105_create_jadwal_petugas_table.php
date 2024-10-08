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
        Schema::create('jadwal_petugas', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('petugas1_id')->nullable()->constrained('users');
            $table->foreignId('petugas2_id')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_petugas');
    }
};

