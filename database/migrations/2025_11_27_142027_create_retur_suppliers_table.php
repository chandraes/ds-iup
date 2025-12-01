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
        Schema::create('retur_suppliers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('nomor');
            $table->foreignId('barang_unit_id')->nullable()->constrained('barang_units')->onDelete('set null');
            $table->text('keterangan')->nullable();
            $table->integer('tipe')->default(0)->comment('99: void');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retur_suppliers');
    }
};
