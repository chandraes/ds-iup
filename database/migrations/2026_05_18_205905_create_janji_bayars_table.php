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
        Schema::create('janji_bayars', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->foreignId('konsumen_id')->nullable()->constrained('konsumens')->nullOnDelete();
            $table->string('metode');
            $table->bigInteger('nominal');
            $table->date('jatuh_tempo');
            $table->integer('status')->default(0); // 0 = Belum Lunas, 1 = Lunas
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('janji_bayars');
    }
};
