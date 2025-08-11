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
        Schema::table('invoice_jual_sales_details', function (Blueprint $table) {
            $table->boolean('is_grosir')->default(0)->after('jumlah')->comment('Penjualan grosir');
            $table->integer('jumlah_grosir')->nullable()->after('is_grosir')->comment('Jumlah grosir yang dibeli');
            $table->foreignId('satuan_grosir_id')->nullable()->after('jumlah_grosir')
                ->constrained('satuans')->nullOnDelete()->comment('Satuan grosir yang digunakan');
            $table->bigInteger('diskon')->default(0)->after('harga_satuan');
            $table->bigInteger('ppn')->default(0)->after('diskon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_jual_sales_details', function (Blueprint $table) {
            $table->dropForeign(['satuan_grosir_id']);
            $table->dropColumn(['is_grosir', 'jumlah_grosir', 'satuan_grosir_id', 'diskon', 'ppn']);
        });
    }
};
