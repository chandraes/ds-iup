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
        Schema::create('stok_retur_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stok_retur_id')
              ->constrained('stok_returs')
              ->onDelete('cascade');

            // Link ke item retur ASLI (untuk traceability)
            $table->foreignId('barang_retur_detail_id')
                ->constrained('barang_retur_details')
                ->onDelete('cascade')
                ->unique(); // 1 item detail hanya bisa masuk 1x

            $table->bigInteger('qty_diterima');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stok_retur_sources');
    }
};
