<?php

use App\Models\KasKonsumen;
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
        Schema::table('kas_konsumens', function (Blueprint $table) {
            $table->foreignId('invoice_jual_id')->nullable()->after('konsumen_id')->constrained('invoice_juals')->onDelete('cascade');
            $table->bigInteger('cash')->nullable()->after('uraian');
        });

        // fix kas konsumen (add Invoice jual id)
        $dbkasKonsumen = new KasKonsumen();
        $kasKonsumen = $dbkasKonsumen->all();

        foreach ($kasKonsumen as $kas) {
            $invoiceJual = InvoiceJual::where('kode', $kas->uraian)->first();
            if ($invoiceJual) {
                $kas->update([
                    'invoice_jual_id' => $invoiceJual->id,
                ]);

                if ($invoiceJual->lunas == 1) {
                    $sisaKas = $dbkasKonsumen->sisaTerakhir($invoiceJual->konsumen_id);
                    $totalInvoice = $invoiceJual->grand_total;
                    $sisa = $sisaKas - $totalInvoice;

                    if ($sisa < 0) {
                        $sisa = 0;
                    }

                    $dbkasKonsumen->create([
                        'konsumen_id' => $invoiceJual->konsumen_id,
                        'invoice_jual_id' => $invoiceJual->id,
                        'uraian' => 'Pelunasan ' . $invoiceJual->kode,
                        'bayar' => $totalInvoice,
                        'sisa' => $sisa,
                    ]);
                }
            }


        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kas_konsumens', function (Blueprint $table) {
            $table->dropForeign(['invoice_jual_id']);
            $table->dropColumn('invoice_jual_id');
            $table->dropColumn('cash');
        });
    }
};
