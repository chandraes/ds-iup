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
        Schema::create('ganti_rugis', function (Blueprint $table) {
            $table->id();
            $table->boolean('kas_ppn');
            $table->boolean('lunas')->default(0);
            $table->foreignId('karyawan_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('barang_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('barang_stok_harga_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('jumlah');
            $table->float('harga', 20, 2);
            $table->float('total', 20, 2);
            $table->float('total_bayar', 20, 2);
            $table->float('sisa', 20, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ganti_rugis');
    }
};
