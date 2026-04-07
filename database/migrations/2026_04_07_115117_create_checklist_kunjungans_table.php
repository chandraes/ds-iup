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
        Schema::create('checklist_kunjungans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('konsumen_id')->constrained('konsumens')->cascadeOnDelete();
            $table->integer('tahun');
            $table->integer('bulan');
            $table->enum('status', ['visited', 'not_visited']);
            $table->timestamps();

            // Mencegah duplikasi data per toko di bulan & tahun yang sama
            $table->unique(['konsumen_id', 'tahun', 'bulan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checklist_kunjungans');
    }
};
