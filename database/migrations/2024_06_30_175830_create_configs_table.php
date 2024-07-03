<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Config;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('configs', function (Blueprint $table) {
            $table->id();
            $table->string('untuk');
            $table->string('nama')->nullable();
            $table->string('singkatan')->nullable();
            $table->text('alamat')->nullable();
            $table->string('kode_pos')->nullable();
            $table->text('logo')->default('default.png');
            $table->string('nama_direktur')->nullable();
            $table->timestamps();
        });

        $data = [
            [
                'untuk' => 'resmi',
                'nama' => 'PT. Nama Perusahaan',
                'singkatan' => 'NP',
                'alamat' => 'Jl. Nama Jalan No. 1, Kota, Provinsi',
                'kode_pos' => '12345',
                'nama_direktur' => 'Nama Direktur',
            ],
            [
                'untuk' => 'non-resmi',
                'nama' => 'Nama Perusahaan',
                'singkatan' => 'NP',
                'alamat' => 'Jl. Nama Jalan No. 1, Kota, Provinsi',
                'kode_pos' => '12345',
                'nama_direktur' => 'Nama Direktur',
            ],
        ];

        Config::insert($data);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configs');
    }
};
