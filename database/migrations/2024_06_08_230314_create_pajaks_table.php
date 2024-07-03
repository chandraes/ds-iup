<?php

use App\Models\db\Pajak;
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
        Schema::create('pajaks', function (Blueprint $table) {
            $table->id();
            $table->string('untuk');
            $table->float('persen');
            $table->timestamps();
        });

       $data = [
            ['untuk' => 'PPn', 'persen' => 11],
            ['untuk' => 'PPh', 'persen' => 2],
        ];

        foreach ($data as $key => $value) {
            Pajak::create($value);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pajaks');
    }
};
