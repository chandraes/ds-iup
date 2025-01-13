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
            $table->boolean('titipan')->default(0)->after('lunas');
        });

        Schema::table('kas_konsumens', function (Blueprint $table) {
            $table->bigInteger('titipan')->nullable()->after('hutang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_juals', function (Blueprint $table) {
            $table->dropColumn('titipan');
        });

        Schema::table('kas_konsumens', function (Blueprint $table) {
            $table->dropColumn('titipan');
        });
    }
};
