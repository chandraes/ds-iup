<?php

use App\Models\Pengaturan;
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
        Schema::table('keranjang_juals', function (Blueprint $table) {
            $table->boolean('stok_kurang')->default(0)->after('total');
        });

        Pengaturan::create([
            'untuk' => 'penyesuaian_jual',
            'nilai' => '1000',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('keranjang_juals', function (Blueprint $table) {
            $table->dropColumn('stok_kurang');
        });

        $pengaturan = Pengaturan::where('untuk', 'penyesuaian_jual')->first();

        if ($pengaturan) {
            $pengaturan->delete();
        }
        // Hapus data pengaturan jika ada
    }
};
