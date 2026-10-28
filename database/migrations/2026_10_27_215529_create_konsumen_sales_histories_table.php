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
        Schema::create('konsumen_sales_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('konsumen_id');
            $table->unsignedBigInteger('sales_lama_id')->nullable();
            $table->unsignedBigInteger('sales_baru_id');
            $table->string('sales_lama')->nullable();
            $table->string('sales_baru');
            $table->unsignedBigInteger('updated_by');
            $table->string('updated_by_name');
            $table->string('keterangan', 500)->nullable();
            $table->timestamps();
            $table->index(['konsumen_id', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('konsumen_sales_histories');
    }
};
