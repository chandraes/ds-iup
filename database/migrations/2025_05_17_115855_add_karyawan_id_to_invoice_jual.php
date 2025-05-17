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
        $data = InvoiceJual::with(['konsumen.karyawan'])->where('void', 0)->whereNotNull('konsumen_id')->get();

        foreach ($data as $d) {
            if ($d->konsumen && $d->konsumen->karyawan_id) {
                $d->update(['karyawan_id' => $d->konsumen->karyawan_id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $data = InvoiceJual::with(['konsumen.karyawan'])->where('void', 0)->whereNotNull('konsumen_id')->get();

        foreach ($data as $d) {
            if ($d->konsumen && $d->konsumen->karyawan_id) {
                $d->update(['karyawan_id' => null]);
            }
        }
    }
};
