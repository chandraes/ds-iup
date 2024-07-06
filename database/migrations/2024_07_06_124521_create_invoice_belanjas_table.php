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
        Schema::create('invoice_belanjas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->integer('nomor');
            $table->string('uraian');
            $table->float('diskon', 35, 2)->default(0);
            $table->float('ppn', 35, 2)->default(0);
            $table->float('add_fee', 35, 2)->default(0);
            $table->float('total', 35, 2);
            $table->float('dp', 35, 2)->default(0);
            $table->float('dp_ppn', 35, 2)->default(0);
            $table->float('sisa', 35, 2);
            $table->float('sisa_ppn', 35, 2);
            $table->string('nama_rek');
            $table->string('no_rek');
            $table->string('bank');
            $table->boolean('tempo')->default(0);
            $table->date('jatuh_tempo')->nullable();
            $table->boolean('void')->default(0);
            $table->timestamps();
        });

        Schema::table('barang_histories', function (Blueprint $table) {
            $table->string('uraian')->nullable()->after('id');
            $table->foreignId('invoice_belanja_id')->nullable()->after('harga')->constrained()->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barang_histories', function (Blueprint $table) {
            $table->dropColumn('uraian');
            $table->dropForeign(['invoice_belanja_id']);
            $table->dropColumn('invoice_belanja_id');
        });

        Schema::dropIfExists('invoice_belanjas');
    }
};
