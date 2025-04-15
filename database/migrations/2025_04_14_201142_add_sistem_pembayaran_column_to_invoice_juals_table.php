<?php

use App\Models\transaksi\InvoiceJual;
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
        Schema::table('invoice_juals', function (Blueprint $table) {
            $table->integer('sistem_pembayaran')->default(1)->after('id')->comment('1 = Cash, 2 = Tempo, 3 = Titipan');
        });

        InvoiceJual::where('lunas', 0)
            ->where('void', 0)
            ->chunk(100, function ($data) {
                foreach ($data as $d) {
                    $d->sistem_pembayaran = $d->titipan === 1 ? 3 : 2;
                    $d->save();
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_juals', function (Blueprint $table) {
            $table->dropColumn('sistem_pembayaran');
        });
    }
};
