<?php

use App\Models\GroupWa;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Group;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('barang_returs', function (Blueprint $table) {
            $table->id();
            $table->integer('tipe');
            $table->foreignId('barang_unit_id')->constrained('barang_units')->onDelete('cascade');
            $table->foreignId('konsumen_id')->nullable()->constrained('konsumens')->onDelete('cascade');
            $table->integer('status')->default(0)->comment('0: pending, 1: diajukan, 2: diproses, 3: selesai');
            $table->timestamps();
        });

        GroupWa::create([
            'untuk' => 'barang-retur',
            'nama_group' => 'Testing Group',
            'group_id' => 'Testing Group'
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang_returs');
        GroupWa::where('untuk', 'barang-retur')->delete();
    }
};
