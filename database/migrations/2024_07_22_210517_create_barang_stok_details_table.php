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
        Schema::create('barang_stok_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_stok_harga_id')->constrained('barang_stok_hargas');
            $table->string('kode');
            $table->boolean('jual')->default(0);
            $table->timestamps();
        });

        Schema::table('barangs', function (Blueprint $table) {
            $table->boolean('detail')->default(0)->comment('0: Tidak ada detail, 1: Ada detail')->after('jenis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang_stok_details');

        Schema::table('barangs', function (Blueprint $table) {
            $table->dropColumn('detail');
        });
    }
};
