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
        Schema::table('ppn_masukans', function (Blueprint $table) {
            $table->boolean('is_faktur')->default(0)->after('saldo');
            $table->string('no_faktur')->nullable()->after('is_faktur');
            $table->boolean('is_keranjang')->default(0)->after('no_faktur');
            $table->boolean('is_finish')->default(0)->after('is_keranjang');
        });

        Schema::table('ppn_keluarans', function (Blueprint $table) {
            $table->boolean('is_faktur')->default(0)->after('saldo');
            $table->string('no_faktur')->nullable()->after('is_faktur');
            $table->boolean('is_keranjang')->default(0)->after('no_faktur');
            $table->boolean('is_finish')->default(0)->after('is_keranjang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ppn_masukans', function (Blueprint $table) {
            $table->dropColumn('is_faktur');
            $table->dropColumn('no_faktur');
            $table->dropColumn('is_keranjang');
            $table->dropColumn('is_finish');
        });

        Schema::table('ppn_keluarans', function (Blueprint $table) {
            $table->dropColumn('is_faktur');
            $table->dropColumn('no_faktur');
            $table->dropColumn('is_keranjang');
            $table->dropColumn('is_finish');
        });
    }
};
