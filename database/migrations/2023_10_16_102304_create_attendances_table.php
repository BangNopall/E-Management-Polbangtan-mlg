<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('');
            $table->string('date');
            $table->time('start_time')->default('06:00:00'); // mulai absen masuk
            $table->time('end_time')->default('22:00:00'); // mulai absen pulang'
            $table->timestamps();
        });      
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};