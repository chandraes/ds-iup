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
        Schema::create('barang_retur_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_retur_id')->constrained('barang_returs')->onDelete('cascade');

            // --- AWAL PERUBAHAN LOGIKA HYBRID ---

            // WAJIB diisi untuk Tipe 1 & 2 (untuk referensi produk)
            $table->foreignId('barang_id')->constrained('barangs')->onDelete('cascade');

            // HANYA diisi untuk Tipe 1 (Retur ke Supplier per Batch)
            $table->foreignId('barang_stok_harga_id')
                  ->nullable()
                  ->constrained('barang_stok_hargas')
                  ->onDelete('cascade');

            // Unique key ini memastikan:
            // 1. Tipe 2 (stok_id=NULL) hanya bisa 1x per barang.
            // 2. Tipe 1 (stok_id=100) hanya bisa 1x per batch.
            // 3. Tipe 1 (stok_id=101) bisa ditambahkan meski barang_id sama.
            $table->unique(
                ['barang_retur_id', 'barang_id', 'barang_stok_harga_id'],
                'brd_retur_barang_stok_uq' // 'brd' = barang retur detail, 'uq' = unique
            );

            // --- AKHIR PERUBAHAN ---

            $table->integer('qty');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang_retur_details');
    }
};
