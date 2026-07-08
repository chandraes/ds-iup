<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('retur_supplier_receipt_details', function (Blueprint $table) {
            // enum atau string untuk membedakan 'terima' (masuk stok) dan 'hapus' (batal retur)
            $table->string('status_proses')->default('terima')->after('qty_terima');
            $table->string('catatan_item')->nullable()->after('status_proses');
        });
    }

    public function down()
    {
        Schema::table('retur_supplier_receipt_details', function (Blueprint $table) {
            $table->dropColumn(['status_proses', 'catatan_item']);
        });
    }
};
