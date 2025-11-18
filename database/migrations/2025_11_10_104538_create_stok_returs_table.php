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
        Schema::create('stok_returs', function (Blueprint $table) {
            $table->id();

            // --- AWAL PERUBAHAN LOGIKA HYBRID ---

            // WAJIB diisi (untuk referensi produk)
            $table->foreignId('barang_id')
                  ->constrained('barangs')
                  ->onDelete('cascade');

            // HANYA diisi untuk Tipe 1
            $table->foreignId('barang_stok_harga_id')
                  ->nullable()
                  ->constrained('barang_stok_hargas')
                  ->onDelete('cascade');

            // Unique key untuk Karantina
            // 1 baris untuk Tipe 2 (stok_id=NULL)
            // 1 baris per batch untuk Tipe 1
            $table->unique(
                ['barang_id', 'barang_stok_harga_id'],
                'sr_barang_stok_uq' // 'sr' = stok retur, 'uq' = unique
            );

            // --- AKHIR PERUBAHAN ---

            $table->bigInteger('total_qty_karantina')->default(0);
            $table->bigInteger('total_qty_diproses')->default(0);
            $table->integer('status')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stok_returs');
    }
};
