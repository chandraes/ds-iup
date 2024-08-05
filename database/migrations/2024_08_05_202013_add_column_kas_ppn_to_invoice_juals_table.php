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
        Schema::table('invoice_juals', function (Blueprint $table) {
            $table->boolean('kas_ppn')->default(1)->after('void');
            $table->dropColumn('pph');
            $table->dropColumn('diskon');
        });
        Schema::table('invoice_juals', function (Blueprint $table) {
            $table->integer('diskon')->default(0)->after('total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_juals', function (Blueprint $table) {
            $table->dropColumn('kas_ppn');
            $table->integer('pph')->default(0)->after('ppn');
            $table->integer('diskon')->default(0)->after('pph');
        });
    }
};
