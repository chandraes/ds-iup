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
        Schema::create('invoice_jual_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_jual_id')->constrained('invoice_juals')->onDelete('cascade');
            $table->foreignId('barang_id')->nullable()->constrained('barangs')->onDelete('set null');
            $table->foreignId('barang_stok_harga_id')->nullable()->constrained('barang_stok_hargas')->onDelete('set null');
            $table->integer('jumlah');
            $table->integer('harga_satuan');
            $table->integer('total');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_jual_details');
    }
};
