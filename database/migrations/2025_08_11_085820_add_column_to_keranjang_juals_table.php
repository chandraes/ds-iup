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
            $table->bigInteger('total_diskon')->default(0)->after('total_ppn')->comment('diskon x jumlah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('keranjang_juals', function (Blueprint $table) {
            $table->dropColumn('total_diskon');
        });
    }
};
