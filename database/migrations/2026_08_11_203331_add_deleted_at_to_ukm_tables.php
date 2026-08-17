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
        Schema::table('ukms', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('ukm_members', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('ukm_jadwals', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('ukm_presensis', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ukm_presensis', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('ukm_jadwals', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('ukm_members', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('ukms', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
