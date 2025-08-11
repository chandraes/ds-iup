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
        Schema::table('keranjang_juals', function (Blueprint $table) {
            $table->foreignId('keranjang_jual_konsumen_id')
                ->after('id')
                ->constrained('keranjang_jual_konsumens')
                ->onDelete('cascade');

            // drop foreign user id that constrained to users table
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('keranjang_juals', function (Blueprint $table) {
            $table->dropForeign(['keranjang_jual_konsumen_id']);
            $table->dropColumn('keranjang_jual_konsumen_id');

            // add foreign user id that constrained to users table
            $table->foreignId('user_id')
                ->after('id')
                ->constrained()
                ->onDelete('cascade');
        });
    }
};
