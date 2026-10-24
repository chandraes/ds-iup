<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('karyawans')->whereIn('jabatan_id', DB::table('jabatans')->select('id')->whereRaw('LOWER(TRIM(nama)) = ?', ['sales']))
            ->update(['persen_komisi' => 4, 'tunjangan_bbm' => 15000]);
    }

    public function down(): void
    {
        // Preserve compensation values that may have been edited after migration.
    }
};
