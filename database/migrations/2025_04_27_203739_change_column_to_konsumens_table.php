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
            // drop column kode toko id
            $table->dropForeign(['sales_area_id']);
            $table->dropColumn('sales_area_id');

            $table->foreignId('karyawan_id')->after('id')->nullable()->constrained('karyawans')->nullOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('konsumens', function (Blueprint $table) {
            $table->dropForeign(['karyawan_id']);
            $table->dropColumn('karyawan_id');

            $table->foreignId('sales_area_id')->after('id')->nullable()->constrained('sales_areas')->nullOnDelete();
        });
    }
};
