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
        Schema::create('invoice_jual_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->nullable()->constrained('karyawans')->onDelete('set null');
            $table->integer('sistem_pembayaran')->comment('1 = Cash, 2 = Tempo, 3 = Titipan');
            $table->boolean('is_finished')->default(0);
            $table->boolean('kas_ppn');
            $table->foreignId('konsumen_id')->nullable()->constrained('konsumens')->onDelete('set null');
            $table->bigInteger('total')->default(0);
            $table->bigInteger('diskon')->default(0);
            $table->bigInteger('ppn')->default(0);
            $table->bigInteger('add_fee')->default(0);
            $table->bigInteger('grand_total')->default(0);
            $table->bigInteger('dp')->default(0);
            $table->bigInteger('dp_ppn')->default(0);
            $table->bigInteger('sisa_tagihan')->default(0);
            $table->bigInteger('sisa_ppn')->default(0);
            $table->boolean('ppn_dipungut');
            $table->timestamps();
        });

        Schema::table('invoice_juals', function (Blueprint $table) {
            $table->foreignId('karyawan_id')->nullable()->constrained('karyawans')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::table('invoice_juals', function (Blueprint $table) {
            $table->dropForeign(['karyawan_id']);
            $table->dropColumn('karyawan_id');
        });

        Schema::dropIfExists('invoice_jual_sales');


    }
};
