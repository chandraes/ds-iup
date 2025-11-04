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
        Schema::table('barang_returs', function (Blueprint $table) {
            $table->dateTime('waktu_diterima')->nullable()->after('status');
            $table->dateTime('waktu_diproses')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barang_returs', function (Blueprint $table) {
            $table->dropColumn(['waktu_diterima', 'waktu_diproses']);
        });
    }
};
