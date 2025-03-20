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
            $table->integer('min_jual')->nullable()->after('harga_beli');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barang_stok_hargas', function (Blueprint $table) {
            $table->dropColumn('min_jual');
        });
    }
};
