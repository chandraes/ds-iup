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
        Schema::create('kas_besars', function (Blueprint $table) {
            $table->id();
            $table->string('uraian')->nullable();
            $table->integer('nomor_deposit')->nullable();
            $table->bigInteger('nomor_kode_kas_kecil')->nullable();
            $table->boolean('jenis');
            $table->bigInteger('nominal');
            $table->bigInteger('saldo');
            $table->string('no_rek')->nullable();
            $table->string('nama_rek')->nullable();
            $table->string('bank')->nullable();
            $table->bigInteger('modal_investor')->nullable();
            $table->bigInteger('modal_investor_terakhir');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kas_besars');
    }
};
