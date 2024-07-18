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
        Schema::table('barang_stok_hargas', function (Blueprint $table) {
            $table->dropColumn('tipe');
            $table->float('harga_beli', 25, 2)->default(0)->after('harga');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barang_stok_hargas', function (Blueprint $table) {
            $table->dropColumn('harga_beli');
            $table->enum('tipe', ['ppn', 'non-ppn'])->after('barang_id');
        });
    }
};
