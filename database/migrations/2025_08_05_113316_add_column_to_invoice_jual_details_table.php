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
        Schema::table('invoice_jual_details', function (Blueprint $table) {
            $table->bigInteger('diskon')->default(0)->after('harga_satuan');
            $table->bigInteger('ppn')->default(0)->after('diskon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_jual_details', function (Blueprint $table) {
            $table->dropColumn(['diskon', 'ppn']);
        });
    }
};
