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
            $table->boolean('wa_notif')->default(0)->after('checklist_kunjungan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('konsumens', function (Blueprint $table) {
            $table->dropColumn('wa_notif');
        });
    }
};
