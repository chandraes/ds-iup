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
            $table->string('provinsi_id')->nullable()->after('no_kantor');
            $table->string('kabupaten_kota_id')->nullable()->after('provinsi_id');
            $table->string('kecamatan_id')->nullable()->after('kabupaten_kota_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('konsumens', function (Blueprint $table) {
            $table->dropColumn('provinsi_id');
            $table->dropColumn('kabupaten_kota_id');
            $table->dropColumn('kecamatan_id');
        });
    }
};
