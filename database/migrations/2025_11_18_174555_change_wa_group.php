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
        $data = GroupWa::where('untuk', 'barang-retur')->first();

        if (!$data) {
            GroupWa::firstOrCreate([
                'untuk' => 'terima-barang-retur',
                'nama_group' => "Testing Group",
                'group_id' => "Testing Group"
            ]);
        } else {
            $data->update(['untuk' => 'terima-barang-retur']);
        }

        GroupWa::firstOrCreate(['untuk'=> 'kirim-barang-retur', 'nama_group' => "Testing Group",
                'group_id' => "Testing Group"]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
