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
            $table->boolean('ppn_dipungut')->default(1)->after('jatuh_tempo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_juals', function (Blueprint $table) {
            $table->dropColumn('ppn_dipungut');
        });
    }
};
