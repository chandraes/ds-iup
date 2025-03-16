<?php

use App\Models\transaksi\InvoiceJual;
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
        Schema::table('invoice_juals', function (Blueprint $table) {
            $table->bigInteger('sisa_tagihan')->default(0)->after('dp_ppn');
            $table->bigInteger('sisa_ppn')->default(0)->after('sisa_tagihan');
        });

        // add value to sisa_tagihan and sisa_ppn
        $data = InvoiceJual::where('lunas', 0)->get();

        foreach ($data as $item) {
            $item->sisa_tagihan = $item->ppn_dipungut == 1 ? $item->grand_total - $item->dp - $item->dp_ppn : $item->grand_total - $item->dp;
            $item->sisa_ppn = $item->ppn - $item->dp_ppn;
            $item->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_juals', function (Blueprint $table) {
            $table->dropColumn('sisa_tagihan');
            $table->dropColumn('sisa_ppn');
        });
    }
};
