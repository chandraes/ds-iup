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
        Schema::create('uang_gantungs', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->bigInteger('nominal');
            $table->string('keterangan')->nullable();
            $table->boolean('ppn_kas');
            $table->boolean('lunas')->default(false);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uang_gantungs');
    }
};
