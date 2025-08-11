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
        Schema::table('keranjang_juals', function (Blueprint $table) {
            $table->bigInteger('diskon')->default(0)->after('harga_satuan')->comment('Diskon yang diberikan pada keranjang jual');
            $table->bigInteger('ppn')->default(0)->after('diskon')->comment('PPN yang dikenakan pada keranjang jual');
            $table->bigInteger('harga_satuan_akhir')->default(0)->after('ppn')->comment('Harga satuan akhir setelah diskon dan PPN');
            $table->bigInteger('total_ppn')->default(0)->after('ppn')->comment('Total PPN dikali qty yang dikenakan pada keranjang jual');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('keranjang_juals', function (Blueprint $table) {
            $table->dropColumn(['diskon', 'ppn', 'harga_satuan_akhir', 'total_ppn']);
        });
    }
};
