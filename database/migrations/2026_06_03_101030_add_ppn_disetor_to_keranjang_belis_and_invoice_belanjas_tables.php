<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('keranjang_belis', function (Blueprint $table) {
            $table->tinyInteger('ppn_disetor')->nullable()->after('sistem_pembayaran');
        });

        Schema::table('invoice_belanjas', function (Blueprint $table) {
            $table->tinyInteger('ppn_disetor')->nullable()->after('kas_ppn');
        });

        DB::table('keranjang_belis')->where('kas_ppn', 1)->update(['ppn_disetor' => 1]);
        DB::table('keranjang_belis')->where('kas_ppn', '<>', 1)->update(['ppn_disetor' => 0]);

        DB::table('invoice_belanjas')->where('kas_ppn', 1)->update(['ppn_disetor' => 1]);
        DB::table('invoice_belanjas')->where('kas_ppn', '<>', 1)->update(['ppn_disetor' => 0]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('keranjang_belis', function (Blueprint $table) {
            $table->dropColumn('ppn_disetor');
        });

        Schema::table('invoice_belanjas', function (Blueprint $table) {
            $table->dropColumn('ppn_disetor');
        });
    }
};
