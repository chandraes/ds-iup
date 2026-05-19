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
        Schema::create('janji_bayar_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('janji_bayar_id')->constrained('janji_bayars')->cascadeOnDelete();
            $table->foreignId('invoice_jual_id')->constrained('invoice_juals')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('janji_bayar_details');
    }
};
