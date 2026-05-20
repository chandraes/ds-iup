<?php

use App\Models\MetodeBayar;
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
        Schema::create('metode_bayars', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // Untuk pencarian di kode (giro, cek, dll)
            $table->string('nama');           // Nama tampilan (Giro, Cek, Tanda Terima, Slip)
            $table->integer('max_hari')->default(0); // Batasan +hari jatuh tempo
            $table->timestamps();
        });

        $data = [
            ['slug' => 'giro', 'nama' => 'Giro', 'max_hari' => 30],
            ['slug' => 'cek', 'nama' => 'Cek', 'max_hari' => 30],
            ['slug' => 'tanda_terima', 'nama' => 'Tanda Terima', 'max_hari' => 14],
            ['slug' => 'slip', 'nama' => 'Slip', 'max_hari' => 7],
        ];

        foreach ($data as $item) {
            MetodeBayar::updateOrCreate(['slug' => $item['slug']], $item);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metode_bayars');
    }
};
