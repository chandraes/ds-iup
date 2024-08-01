<?php

use App\Models\db\Barang\Barang;
use App\Models\db\Barang\BarangStokHarga;
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
        Schema::table('barangs', function (Blueprint $table) {
            $table->foreignId('barang_unit_id')->nullable()->after('id')->constrained('barang_units')->onDelete('set null');
        });

        $barangData = Barang::all();

        foreach ($barangData as $d) {
            $d->barang_unit_id = $d->type->barang_unit_id;
            $d->save();
        }

        Schema::table('barang_stok_hargas', function (Blueprint $table) {
            $table->foreignId('barang_unit_id')->nullable()->after('id')->constrained('barang_units')->onDelete('set null');
            $table->foreignId('barang_type_id')->nullable()->after('barang_unit_id')->constrained('barang_types')->onDelete('set null');
            $table->foreignId('barang_kategori_id')->nullable()->after('barang_type_id')->constrained('barang_kategoris')->onDelete('set null');
            $table->foreignId('barang_nama_id')->nullable()->after('barang_kategori_id')->constrained('barang_namas')->onDelete('set null');
        });

        $stok = BarangStokHarga::all();

        foreach ($stok as $s) {
            $s->barang_unit_id = $s->barang->type->barang_unit_id;
            $s->barang_type_id = $s->barang->barang_type_id;
            $s->barang_kategori_id = $s->barang->barang_kategori_id;
            $s->barang_nama_id = $s->barang->barang_nama_id;
            $s->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barang_stok_hargas', function (Blueprint $table) {
            $table->dropForeign(['barang_unit_id']);
            $table->dropColumn('barang_unit_id');
            $table->dropForeign(['barang_type_id']);
            $table->dropColumn('barang_type_id');
            $table->dropForeign(['barang_kategori_id']);
            $table->dropColumn('barang_kategori_id');
            $table->dropForeign(['barang_nama_id']);
            $table->dropColumn('barang_nama_id');
        });

        Schema::table('barangs', function (Blueprint $table) {
            $table->dropForeign(['barang_unit_id']);
            $table->dropColumn('barang_unit_id');
        });


    }
};
