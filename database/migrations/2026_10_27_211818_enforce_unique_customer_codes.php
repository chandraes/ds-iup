<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('konsumens', function (Blueprint $table): void {
            $table->unique('kode');
            $table->index('no_hp');
        });
    }

    public function down(): void
    {
        Schema::table('konsumens', function (Blueprint $table): void {
            $table->dropUnique(['kode']);
            $table->dropIndex(['no_hp']);
        });
    }
};
