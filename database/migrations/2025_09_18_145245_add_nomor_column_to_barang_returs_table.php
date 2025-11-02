<?php

use App\Models\BarangRetur;
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
        Schema::table('barang_returs', function (Blueprint $table) {
            $table->integer('nomor')->after('id')->nullable();
        });

        // Update existing records to have sequential 'nomor' values
        $barangReturs = BarangRetur::orderBy('created_at')->get();
        $nomor = 1;
        foreach ($barangReturs as $retur) {
            $retur->nomor = $nomor++;
            $retur->save();
        }
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barang_returs', function (Blueprint $table) {
            $table->dropColumn('nomor');
        });
    }
};
