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
            $table->foreignId('barang_stok_harga_id')
              ->constrained('barang_stok_hargas')
              ->onDelete('cascade')
              ->unique(); // <-- Kunci: 1 baris per barang

            // Total QTY di karantina
            $table->bigInteger('total_qty_karantina')->default(0);

            // QTY yang sudah diproses (misal, dikembalikan ke supplier)
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
