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
        Schema::create('inventaris_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventaris_id')->nullable()->constrained('inventaris_rekaps')->onDelete('set null');
            $table->integer('pembayaran')->comment('1: cash, 2: tempo, 3: kredit');
            $table->integer('jumlah');
            $table->integer('harga_satuan');
            $table->integer('ppn')->default(0);
            $table->integer('total');
            $table->integer('dp')->default(0);
            $table->integer('lama_cicilan')->default(0);
            $table->integer('nominal_cicilan')->default(0);
            $table->integer('sisa_cicilan')->default(0);
            $table->date('tanggal_jatuh_tempo')->nullable();
            $table->string('no_rek');
            $table->string('bank');
            $table->string('nama_rek');
            $table->boolean('lunas')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventaris_invoices');
    }
};
