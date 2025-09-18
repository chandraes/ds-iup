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
            $table->foreignId('barang_stok_harga_id')->constrained('barang_stok_hargas')->onDelete('cascade');
            $table->unique(['barang_retur_id', 'barang_stok_harga_id']);
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
