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
        // 1. Buat Table stok_returs (Gudang Karantina / Bad Stock)
        // Table ini bersifat AGREGAT (1 Barang = 1 Baris Data)
        Schema::create('stok_returs', function (Blueprint $table) {
            $table->id();

            // Relasi ke table barangs
            $table->foreignId('barang_id')
                  ->constrained('barangs')
                  ->onDelete('cascade');

            // Kolom akumulasi stok
            $table->bigInteger('total_qty_karantina')->default(0);
            $table->bigInteger('total_qty_diproses')->default(0);

            // Status (Misal: 0=Karantina, 1=Siap Retur ke Suplier, dst)
            $table->integer('status')->default(0);

            $table->timestamps();

            // UNIQUE KEY PENTING:
            // Memastikan 1 barang hanya punya 1 baris record di gudang ini.
            $table->unique('barang_id', 'sr_barang_unique');
        });

        // 2. Buat Table stok_retur_sources (Jejak Audit / History)
        // Table ini mencatat DETAIL dari mana bad stock itu berasal (Batch ID nya)
        Schema::create('stok_retur_sources', function (Blueprint $table) {
            $table->id();

            // Link ke Gudang Karantina
            $table->foreignId('stok_retur_id')
                  ->constrained('stok_returs')
                  ->onDelete('cascade');

            // Link ke Transaksi Retur Konsumen
            $table->foreignId('barang_retur_detail_id')
                  ->constrained('barang_retur_details')
                  ->onDelete('cascade');

            // Link ke Asal Batch Gudang Utama (Traceability)
            // Nullable & NullOnDelete: Jika batch gudang utama dihapus, history ini tetap ada (ID jadi null)
            $table->foreignId('barang_stok_harga_id')
                  ->nullable()
                  ->constrained('barang_stok_hargas')
                  ->nullOnDelete();

            $table->bigInteger('qty_diterima'); // Jumlah yang diambil dari batch tersebut

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop urut dari child (sources) baru parent (stok_returs)
        Schema::dropIfExists('stok_retur_sources');
        Schema::dropIfExists('stok_returs');
    }
};
