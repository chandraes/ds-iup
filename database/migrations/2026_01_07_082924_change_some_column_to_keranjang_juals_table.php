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
            $table->decimal('ppn', 10, 2)->change();
            $table->decimal('total_ppn', 10, 2)->change();
        });

        Schema::table('invoice_jual_sales_details', function (Blueprint $table) {
            $table->decimal('ppn', 10, 2)->change();
        });

         Schema::table('invoice_jual_details', function (Blueprint $table) {
            $table->decimal('ppn', 10, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('keranjang_juals', function (Blueprint $table) {
            $table->integer('ppn')->change();
            $table->integer('total_ppn')->change();
        });

         Schema::table('invoice_jual_sales_details', function (Blueprint $table) {
            $table->integer('ppn')->change();
        });

         Schema::table('invoice_jual_details', function (Blueprint $table) {
            $table->integer('ppn')->change();
        });
    }
};
