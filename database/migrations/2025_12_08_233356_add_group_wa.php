<?php

use App\Models\GroupWa;
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
        $data = GroupWa::where('untuk', 'kirim-retur-supplier')->first();

        if (!$data) {
            GroupWa::create([
                'untuk'=> 'kirim-retur-supplier', 'nama_group' => "Testing Group",
                'group_id' => "Testing Group"
            ]);
        }

        $data = GroupWa::where('untuk', 'terima-retur-supplier')->first();

         if (!$data) {
            GroupWa::create([
                'untuk'=> 'terima-retur-supplier', 'nama_group' => "Testing Group",
                'group_id' => "Testing Group"
            ]);
        }


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
