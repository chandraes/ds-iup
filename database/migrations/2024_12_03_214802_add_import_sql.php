<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // get sql file from public path
        $sql = file_get_contents(public_path('dbImport/level_wilayahs.sql'));
        DB::unprepared($sql);

        $wilayah = file_get_contents(public_path('dbImport/wilayahs.sql'));
        DB::unprepared($wilayah);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // drop table
        Schema::dropIfExists('wilayahs');
        Schema::dropIfExists('level_wilayahs');
    }
};
