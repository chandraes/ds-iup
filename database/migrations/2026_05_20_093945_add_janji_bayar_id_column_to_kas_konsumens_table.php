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
        Schema::table('kas_konsumens', function (Blueprint $table) {
            $table->foreignId('janji_bayar_id')->nullable()->after('invoice_jual_id')
                    ->constrained('janji_bayars')
                    ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kas_konsumens', function (Blueprint $table) {
            $table->dropConstrainedForeignId('janji_bayar_id');
        });
    }
};
