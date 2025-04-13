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
        Schema::table('kas_besars', function (Blueprint $table) {
            $table->foreignId('invoice_belanja_id')->nullable()->constrained('invoice_belanjas')->onDelete('set null');
        });

        Schema::table('barang_histories', function (Blueprint $table) {
            $table->string('nama')->nullable()->after('barang_id');
        });

        Schema::table('invoice_belanjas', function (Blueprint $table) {
            $table->float('sisa', 35, 2)->default(0)->change();
            $table->float('sisa_ppn', 35, 2)->default(0)->change();
            $table->boolean('kas_ppn')->default(1)->after('supplier_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_belanjas', function (Blueprint $table) {
            $table->float('sisa', 35, 2)->change();
            $table->float('sisa_ppn', 35, 2)->change();
            $table->dropColumn('kas_ppn');
        });

        Schema::table('barang_histories', function (Blueprint $table) {
            $table->dropColumn('nama');
        });

        Schema::table('kas_besars', function (Blueprint $table) {
            $table->dropForeign(['invoice_belanja_id']);
            $table->dropColumn('invoice_belanja_id');
        });
    }
};
