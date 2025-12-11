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
        Schema::create('harga_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_stok_harga_id')->constrained('barang_stok_hargas')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->bigInteger('harga_ajuan');
            $table->integer('min_jual_ajuan');
            $table->integer('status');
            $table->timestamps();
            $table->unique('barang_stok_harga_id', 'stok_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harga_submissions');
    }
};
