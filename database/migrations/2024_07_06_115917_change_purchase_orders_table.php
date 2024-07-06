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
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreignId('supplier_id')->after('id')->constrained()->cascadeOnDelete();

        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->foreignId('barang_id')->after('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->dropColumn('kategori');
            $table->dropColumn('nama_barang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn('supplier_id');
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropForeign(['barang_id']);
            $table->dropColumn('barang_id');
            $table->string('kategori');
            $table->string('nama_barang');
        });
    }
};
