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
        Schema::create('invoice_jual_sales_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_jual_sales_id')->constrained('invoice_jual_sales')->onDelete('cascade');
            $table->foreignId('barang_id')->nullable()->constrained('barangs')->onDelete('set null');
            $table->foreignId('barang_stok_harga_id')->nullable()->constrained('barang_stok_hargas')->onDelete('set null');
            $table->integer('jumlah')->default(0);
            $table->bigInteger('harga_satuan')->default(0);
            $table->bigInteger('total')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        
        Schema::dropIfExists('invoice_jual_sales_details');
    }
};
