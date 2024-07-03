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
        Schema::create('kas_kecils', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('nomor_kode_kas_kecil')->nullable();
            $table->string('uraian')->nullable();
            $table->boolean('jenis');
            $table->bigInteger('nominal');
            $table->bigInteger('saldo');
            $table->string('nama_rek')->nullable();
            $table->string('bank')->nullable();
            $table->string('no_rek')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kas_kecils');
    }
};
