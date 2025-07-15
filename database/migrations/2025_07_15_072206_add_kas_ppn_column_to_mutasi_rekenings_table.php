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
        Schema::table('mutasi_rekenings', function (Blueprint $table) {
            $table->boolean('kas_ppn')->after('id')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mutasi_rekenings', function (Blueprint $table) {
            $table->dropColumn('kas_ppn');
        });
    }
};
