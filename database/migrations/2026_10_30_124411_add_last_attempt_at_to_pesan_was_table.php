<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesan_was', function (Blueprint $table): void {
            $table->timestamp('last_attempt_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pesan_was', function (Blueprint $table): void {
            $table->dropColumn('last_attempt_at');
        });
    }
};
