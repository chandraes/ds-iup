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

        Schema::create('order_indens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_jual_sales_id')->constrained('invoice_jual_sales')->onDelete('cascade');
            $table->foreignId('barang_id')->nullable()->constrained('barangs')->onDelete('set null');
            $table->integer('jumlah');
            $table->boolean('deleted')->default(0);
            $table->timestamps();
        });

        Schema::table('invoice_jual_sales', function (Blueprint $table) {
            $table->integer('total_inden')->default(0)->after('ppn_dipungut');
            $table->boolean('inden_finished')->default(0)->after('total_inden');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_indens');

        Schema::table('invoice_jual_sales', function (Blueprint $table) {
            $table->dropColumn('total_inden');
            $table->dropColumn('inden_finished');
        });
    }
};
