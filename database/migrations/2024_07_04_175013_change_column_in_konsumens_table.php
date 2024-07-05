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
        Schema::table('konsumens', function (Blueprint $table) {
            $table->integer('plafon')->nullable()->change();
            $table->integer('tempo_hari')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('konsumens', function (Blueprint $table) {
            $table->integer('plafon')->nullable(false)->change();
            $table->integer('tempo_hari')->nullable(false)->change();
        });
    }
};
