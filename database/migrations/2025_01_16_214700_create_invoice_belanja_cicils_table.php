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
        Schema::create('invoice_belanja_cicils', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_belanja_id')->constrained('invoice_belanjas')->cascadeOnDelete();
            $table->bigInteger('nominal');
            $table->bigInteger('ppn')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_belanja_cicils');
    }
};
