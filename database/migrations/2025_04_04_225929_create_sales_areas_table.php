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
        Schema::create('sales_areas', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->timestamps();
        });

        Schema::table('konsumens', function (Blueprint $table) {
            $table->foreignId('sales_area_id')->nullable()->after('id')->constrained()->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::table('konsumens', function (Blueprint $table) {
            $table->dropForeign(['sales_area_id']);
            $table->dropColumn('sales_area_id');
        });

        Schema::dropIfExists('sales_areas');
    }
};
