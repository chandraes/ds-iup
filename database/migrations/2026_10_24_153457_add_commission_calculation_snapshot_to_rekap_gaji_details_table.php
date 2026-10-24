<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rekap_gaji_details', function (Blueprint $table): void {
            $table->bigInteger('komisi')->default(0)->change();
            $table->bigInteger('omset_komisi')->nullable();
            $table->decimal('persen_komisi', 5, 2)->nullable();
            $table->json('rincian_komisi')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('rekap_gaji_details', function (Blueprint $table): void {
            $table->dropColumn(['omset_komisi', 'persen_komisi', 'rincian_komisi']);
            $table->unsignedBigInteger('komisi')->default(0)->change();
        });
    }
};
