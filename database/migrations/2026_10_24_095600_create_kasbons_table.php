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
        Schema::create('kasbons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawans')->restrictOnDelete();
            $table->string('jenis', 20);
            $table->date('tanggal');
            $table->unsignedBigInteger('nominal');
            $table->unsignedBigInteger('total_bayar')->default(0);
            $table->unsignedBigInteger('sisa_kas');
            $table->unsignedInteger('cicil_kali')->default(1);
            $table->unsignedBigInteger('cicilan_nominal')->default(0);
            $table->date('mulai_periode');
            $table->boolean('lunas')->default(false);
            $table->boolean('void')->default(false);
            $table->string('alasan_void', 500)->nullable();
            $table->foreignId('kas_besar_id')->constrained('kas_besars')->restrictOnDelete();
            $table->foreignId('void_kas_besar_id')->nullable()->constrained('kas_besars')->restrictOnDelete();
            $table->uuid('submission_token')->unique();
            $table->index(['karyawan_id', 'lunas', 'void']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kasbons');
    }
};
