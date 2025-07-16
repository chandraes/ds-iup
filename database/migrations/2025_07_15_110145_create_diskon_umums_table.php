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
        Schema::create('diskon_umums', function (Blueprint $table) {
            $table->id();
            $table->string('untuk');
            $table->integer('kode')->unique();
            $table->double('persen', 8, 2)->default(0);
            $table->timestamps();
        });

        $data = [
            ['untuk' => 'cash', 'persen' => 3, 'kode' => 1],
            ['untuk' => 'tempo', 'persen' => 0, 'kode' => 2],
            ['untuk' => 'titipan', 'persen' => 0, 'kode' => 3],
        ];

        foreach ($data as $item) {
            \App\Models\db\DiskonUmum::create($item);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diskon_umums');
    }
};
