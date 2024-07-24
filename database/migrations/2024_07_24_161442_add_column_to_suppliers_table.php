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
        Schema::table('suppliers', function (Blueprint $table) {
            $table->integer('pembayaran')->default(2)->after('nama')->comment('1: Cash, 2: Tempo');
            $table->integer('tempo_hari')->nullable()->after('pembayaran');
        });

        Schema::table('keranjang_juals', function (Blueprint $table) {
            $table->foreignId('barang_stok_harga_id')->after('barang_id')->constrained('barang_stok_hargas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('pembayaran');
            $table->dropColumn('tempo_hari');
        });

        Schema::table('keranjang_juals', function (Blueprint $table) {
            $table->dropForeign(['barang_stok_harga_id']);
            $table->dropColumn('barang_stok_harga_id');
        });
    }
};
