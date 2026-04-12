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
        Schema::create('konsumen_plafon_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('konsumen_id')->constrained()->onDelete('cascade');
            $table->bigInteger('nominal_lama');
            $table->bigInteger('nominal_baru');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('konsumen_plafon_histories');
    }
};
