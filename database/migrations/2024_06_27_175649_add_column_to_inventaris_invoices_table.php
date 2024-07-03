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
        Schema::table('inventaris_invoices', function (Blueprint $table) {
            $table->integer('add_fee')->default(0)->after('ppn');
            $table->integer('diskon')->default(0)->after('add_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventaris_invoices', function (Blueprint $table) {
            $table->dropColumn('add_fee');
            $table->dropColumn('diskon');
        });
    }
};
