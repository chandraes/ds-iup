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
        Schema::create('karyawans', function (Blueprint $table) {
            $table->id();
            $table->integer('nomor');
            $table->foreignId('jabatan_id')->constrained('jabatans')->onDelete('cascade');
            $table->string('nama');
            $table->string('nickname');
            $table->integer('gaji_pokok')->default(0);
            $table->integer('tunjangan_jabatan')->default(0);
            $table->integer('tunjangan_keluarga')->default(0);
            $table->string('nik');
            $table->string('npwp');
            $table->string('bpjs_tk');
            $table->string('bpjs_kesehatan');
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->text('alamat');
            $table->string('no_hp');
            $table->string('bank');
            $table->string('no_rek');
            $table->string('nama_rek');
            $table->date('mulai_bekerja');
            $table->string('foto_ktp')->nullable();
            $table->string('foto_diri')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karyawans');
    }
};
