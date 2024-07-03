<?php

namespace App\Http\Controllers;

use App\Models\transaksi\InvoiceBelanja;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $invoices = new InvoiceBelanja();
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');

        $dataTahun = $invoices->dataTahun();

        $data = $invoices->invoiceByMonth($bulan, $tahun);

        $bulanSebelumnya = $bulan - 1;
        $bulanSebelumnya = $bulanSebelumnya == 0 ? 12 : $bulanSebelumnya;
        $tahunSebelumnya = $bulanSebelumnya == 12 ? $tahun - 1 : $tahun;
        $stringBulan = Carbon::createFromDate($tahun, $bulanSebelumnya)->locale('id')->monthName;
        $stringBulanNow = Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;

        return view('rekap.invoice-belanja.index', [
            'data' => $data,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'dataTahun' => $dataTahun,
            'bulanSebelumnya' => $bulanSebelumnya,
            'tahunSebelumnya' => $tahunSebelumnya,
            'stringBulan' => $stringBulan,
            'stringBulanNow' => $stringBulanNow,
        ]);
    }

    public function detail(InvoiceBelanja $invoice)
    {
        return view('rekap.invoice-belanja.detail', [
            'data' => $invoice->load(['rekap', 'rekap.bahan_baku', 'rekap.satuan', 'rekap.bahan_baku.kategori']),
        ]);
    }
}
