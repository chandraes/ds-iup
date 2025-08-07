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
            $table->decimal('diskon_khusus', 10, 2)->default(0)->after('tempo_hari')->comment('Diskon khusus untuk konsumen');
        });

         Schema::table('barangs', function (Blueprint $table) {
            $table->decimal('diskon', 10, 2)->default(0)->after('merk')->comment('Diskon dengan waktu berlaku');
            $table->date('diskon_mulai')->nullable()->after('diskon')->comment('Tanggal mulai diskon');
            $table->date('diskon_selesai')->nullable()->after('diskon_mulai')->comment('Tanggal selesai diskon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('konsumens', function (Blueprint $table) {
            $table->dropColumn('diskon_khusus');
        });

        Schema::table('barangs', function (Blueprint $table) {
            $table->dropColumn(['diskon', 'diskon_mulai', 'diskon_selesai']);
        });
    }
};
