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
        Schema::create('kelompok_rute_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelompok_rute_id')->constrained('kelompok_rutes')->onDelete('cascade');
            $table->foreignId('wilayah_id')->constrained('wilayahs')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelompok_rute_details');
    }
};
