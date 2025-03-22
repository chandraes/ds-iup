<?php

use App\Models\db\Barang\BarangStokHarga;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('barang_stok_hargas', function (Blueprint $table) {
            $table->integer('stok_awal')->default(0)->after('barang_id');
        });

        BarangStokHarga::where('stok', '>', 0)->update([
            'stok_awal' => DB::raw('stok')
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barang_stok_hargas', function (Blueprint $table) {
            $table->dropColumn('stok_awal');
        });
    }
};
