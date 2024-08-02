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
        Schema::create('ppn_keluarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_jual_id')->nullable()->constrained('invoice_juals')->onDelete('set null');
            $table->string('uraian')->nullable();
            $table->integer('nominal');
            $table->integer('saldo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppn_keluarans');
    }
};
