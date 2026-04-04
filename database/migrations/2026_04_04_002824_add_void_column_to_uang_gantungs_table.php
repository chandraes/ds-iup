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
        Schema::table('uang_gantungs', function (Blueprint $table) {
            $table->boolean('void')->default(0)->after('lunas');
            $table->string('void_reason')->nullable()->after('void');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('uang_gantungs', function (Blueprint $table) {
            $table->dropColumn('void');
            $table->dropColumn('void_reason');
        });
    }
};
