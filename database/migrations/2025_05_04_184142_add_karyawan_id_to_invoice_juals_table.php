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


        GroupWa::firstOrCreate([
            'untuk' => 'sales-order',
        ],
        [
            'group_id' => 'Testing Group',
            'nama_group' => '120363151844351865@g.us',
        ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
