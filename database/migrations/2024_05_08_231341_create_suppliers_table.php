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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->boolean('status')->default(1);
            $table->integer('kode');
            $table->string('nama');
            $table->text('alamat');
            $table->string('npwp');
            $table->string('cp');
            $table->string('no_hp');
            $table->string('no_rek');
            $table->string('bank');
            $table->string('nama_rek');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
