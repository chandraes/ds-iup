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
        Schema::table('konsumens', function (Blueprint $table) {
            $table->foreignId('kode_toko_id')->nullable()->after('kode')->constrained('kode_tokos')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('konsumens', function (Blueprint $table) {
            $table->dropForeign(['kode_toko_id']);
            $table->dropColumn('kode_toko_id');
        });
    }
};
