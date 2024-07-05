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
        Schema::create('barang_stok_hargas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained()->onDelete('cascade');
            $table->integer('stok_ppn')->default(0);
            $table->integer('stok_non_ppn')->default(0);
            $table->integer('harga_ppn')->default(0);
            $table->integer('harga_non_ppn')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang_stok_hargas');
    }
};
