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
            $table->integer('dp')->default(0)->after('grand_total');
            $table->integer('dp_ppn')->default(0)->after('dp');
        });
        Schema::table('kas_besars', function (Blueprint $table) {
            $table->foreignId('invoice_jual_id')->nullable()->constrained('invoice_juals')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_juals', function (Blueprint $table) {
            $table->dropColumn('dp');
            $table->dropColumn('dp_ppn');
        });

        Schema::table('kas_besars', function (Blueprint $table) {
            $table->dropConstrainedForeignId('invoice_jual_id');
        });
    }
};
