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
            $table->string('nik')->nullable()->after('nama');
            $table->text('upload_ktp')->nullable()->after('nik');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('konsumens', function (Blueprint $table) {
            $table->dropColumn(['nik', 'upload_ktp']);
        });
    }
};
