<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('riwayat_penyakits');
        Schema::dropIfExists('obats');
        Schema::dropIfExists('penyakits');
        Schema::dropIfExists('rekam_medis');
        Schema::dropIfExists('medical_records');
        Schema::dropIfExists('stok_barangs');
        Schema::dropIfExists('kategori_inventaris');
        Schema::dropIfExists('items');
        Schema::dropIfExists('inventaris');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down migration as these tables are being permanently deleted.
    }
};
