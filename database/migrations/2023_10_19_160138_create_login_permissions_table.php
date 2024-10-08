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
        Schema::create('login_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(); // ID pengguna yang login
            $table->boolean('is_login')->default(false); // Status login (true/false)
            $table->boolean('is_logout')->default(false); // Status login (true/false)
            $table->string('desc_logout')->nullable(); // Status login (true/false)
            $table->date('expiry_date'); // Tanggal kadaluarsa login
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_permissions');
    }
};
