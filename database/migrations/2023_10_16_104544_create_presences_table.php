<?php

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('presences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users'); // Menyesuaikan dengan nama tabel pengguna jika bukan 'users'
            $table->foreignId('attendance_id')->constrained('attendances'); // Menyesuaikan dengan nama tabel absensi jika bukan 'attendances'
            $table->date('presence_date')->nullable();
            $table->time('presence_masuk')->nullable();
            $table->time('presence_keluar')->nullable();
            $table->boolean('is_late')->default(false);
            $table->boolean('is_active')->default(true);
            $table->enum('log_status', ['didalam', 'diluar', 'telat', 'izin', 'non-active'])->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presences');
    }
};
