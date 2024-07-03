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
        Schema::create('inventaris_rekaps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventaris_jenis_id')->nullable()->constrained('inventaris_jenis')->onDelete('set null');
            $table->string('status');
            $table->boolean('jenis');
            $table->string('uraian');
            $table->integer('jumlah');
            $table->bigInteger('harga_satuan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventaris_rekaps');
    }
};
