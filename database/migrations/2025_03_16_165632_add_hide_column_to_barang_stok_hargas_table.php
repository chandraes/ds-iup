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
            $table->boolean('hide')->default(0)->after('harga_beli');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barang_stok_hargas', function (Blueprint $table) {
            $table->dropColumn('hide');
        });
    }
};
