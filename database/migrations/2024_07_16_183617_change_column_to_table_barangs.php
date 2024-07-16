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
        Schema::table('barangs', function (Blueprint $table) {
            $table->dropColumn('nama');
            $table->foreignId('barang_nama_id')->nullable()->after('barang_kategori_id')->constrained('barang_namas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barangs', function (Blueprint $table) {
            $table->string('nama');
            $table->dropForeign(['barang_nama_id']);
            $table->dropColumn('barang_nama_id');
        });
    }
};
