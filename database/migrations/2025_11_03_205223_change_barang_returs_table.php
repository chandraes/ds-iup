<?php

use App\Models\BarangRetur;
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
            $table->dropForeign(['barang_unit_id']);
            $table->dropColumn(['barang_unit_id']);
            $table->foreignId('karyawan_id')->nullable()->after('konsumen_id')->constrained()->onDelete('set null');
        });

        Schema::table('barang_returs', function (Blueprint $table) {
            $table->foreignId('barang_unit_id')->nullable()->after('konsumen_id')->constrained()->onDelete('set null');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barang_returs', function (Blueprint $table) {
            $table->dropForeign(['karyawan_id']);
            $table->dropColumn(['karyawan_id']);
        });
    }
};
