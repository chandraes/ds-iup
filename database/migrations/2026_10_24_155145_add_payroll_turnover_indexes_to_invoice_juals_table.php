<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_juals', function (Blueprint $table): void {
            $table->index(['titipan', 'created_at', 'karyawan_id'], 'invoice_juals_turnover_period_index');
            $table->index(['void', 'titipan', 'updated_at', 'karyawan_id'], 'invoice_juals_void_period_index');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_juals', function (Blueprint $table): void {
            $table->dropIndex('invoice_juals_turnover_period_index');
            $table->dropIndex('invoice_juals_void_period_index');
        });
    }
};
