<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rekap_gaji_details', function (Blueprint $table): void {
            $table->unsignedBigInteger('komisi')->default(0);
            $table->unsignedBigInteger('tunjangan_bbm')->default(0);
        });
        Schema::table('rekap_gajis', function (Blueprint $table): void {
            $table->date('periode_mulai')->nullable();
            $table->date('periode_selesai')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('rekap_gaji_details', function (Blueprint $table): void {
            $table->dropColumn(['komisi', 'tunjangan_bbm']);
        });
        Schema::table('rekap_gajis', function (Blueprint $table): void {
            $table->dropColumn(['periode_mulai', 'periode_selesai']);
        });
    }
};
