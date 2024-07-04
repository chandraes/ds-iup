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
        Schema::table('kas_besars', function (Blueprint $table) {
            $table->boolean('ppn_kas')->after('id');
            $table->index('ppn_kas');
            $table->index(['ppn_kas', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kas_besars', function (Blueprint $table) {
            $table->dropIndex('kas_besars_ppn_kas_created_at_index');
            $table->dropIndex('kas_besars_ppn_kas_index');
            $table->dropColumn('ppn_kas');
        });
    }
};
