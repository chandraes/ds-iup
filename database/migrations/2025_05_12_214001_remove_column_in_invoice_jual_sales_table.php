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
        Schema::table('invoice_jual_sales', function (Blueprint $table) {
            $table->dropColumn('total_inden');
            $table->dropColumn('inden_finished');
        });

        Schema::table('order_indens', function (Blueprint $table) {
            $table->dropForeign(['invoice_jual_sales_id']);
            $table->dropColumn('invoice_jual_sales_id');

            $table->foreignId('konsumen_id')->after('id')->constrained('konsumens')->onDelete('cascade');

            $table->boolean('is_finished')->default(0)->after('konsumen_id');
            $table->dropColumn('deleted');

            $table->dropForeign(['barang_id']);
            $table->dropColumn('barang_id');

            $table->foreignId('karyawan_id')->nullable()->after('id')->constrained('karyawans')->onDelete('set null');


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_jual_sales', function (Blueprint $table) {
            $table->integer('total_inden')->default(0)->after('ppn_dipungut');
            $table->boolean('inden_finished')->default(1);
        });

        Schema::table('order_indens', function (Blueprint $table) {
            $table->dropForeign(['konsumen_id']);
            $table->dropColumn('konsumen_id');

            $table->foreignId('invoice_jual_sales_id')->after('id')->constrained('invoice_jual_sales')->onDelete('cascade');
            $table->dropColumn('is_finished');
            $table->boolean('deleted')->default(0)->after('is_finished');

            $table->foreignId('barang_id')->after('id')->constrained('barangs')->onDelete('cascade');
            $table->dropForeign(['karyawan_id']);
            $table->dropColumn('karyawan_id');
        });
    }
};
