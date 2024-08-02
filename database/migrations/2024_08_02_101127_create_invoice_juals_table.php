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
        Schema::create('invoice_juals', function (Blueprint $table) {
            $table->id();
            $table->integer('nomor');
            $table->string('kode');
            $table->foreignId('konsumen_id')->nullable()->constrained('konsumens')->onDelete('set null');
            $table->foreignId('konsumen_temp_id')->nullable()->constrained('konsumen_temps')->onDelete('set null');
            $table->bigInteger('total');
            $table->bigInteger('ppn')->default(0);
            $table->integer('pph')->default(0);
            $table->bigInteger('diskon')->default(0);
            $table->integer('add_fee')->default(0);
            $table->bigInteger('grand_total');
            $table->date('jatuh_tempo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_juals');
    }
};
