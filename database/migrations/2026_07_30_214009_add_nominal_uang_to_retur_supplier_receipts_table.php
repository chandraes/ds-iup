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
        Schema::table('retur_supplier_receipts', function (Blueprint $table) {
            $table->decimal('nominal_uang', 15, 2)->default(0)->after('catatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('retur_supplier_receipts', function (Blueprint $table) {
            $table->dropColumn('nominal_uang');
        });
    }
};
