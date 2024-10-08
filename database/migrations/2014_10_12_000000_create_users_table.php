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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('nim')->unique()->nullable();
            $table->string('no_kamar')->nullable();
            $table->string('asal_daerah')->nullable();
            $table->string('image')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('no_hp')->unique()->nullable();
            $table->string('password');
            // $table->foreignId('presences_id')->nullable()->constrained('presences')->default(null);
            $table->enum('status', ['didalam', 'diluar', 'telat', 'izin'])->default('didalam');
            $table->integer('poin')->default(0)->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
