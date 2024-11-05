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
        Schema::table('ppn_keluarans', function (Blueprint $table) {
            $table->boolean('dipungut')->default(1)->after('no_faktur');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ppn_keluarans', function (Blueprint $table) {
            $table->dropColumn('dipungut');
        });
    }
};
