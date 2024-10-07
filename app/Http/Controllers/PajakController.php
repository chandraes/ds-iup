<?php

namespace App\Http\Controllers;

use App\Models\PpnKeluaran;
use App\Models\PpnMasukan;
use App\Models\transaksi\InventarisInvoice;
use App\Models\transaksi\InvoiceBelanja;
use Illuminate\Http\Request;

class PajakController extends Controller
{
    public function index()
    {
        return view('pajak.index');
    }

    public function ppn_masukan()
    {
        $db = new PpnMasukan();

        $data = $db->with(['invoiceBelanja'])->get();
        $saldo = $db->saldoTerakhir();

        return view('pajak.ppn-masukan.index', [
            'data' => $data,
            'saldo' => $saldo
        ]);
    }

    public function ppn_keluaran(Request $request)
    {
        $db = new PpnKeluaran();

        $data = $db->with('invoiceJual')->get();
        $saldo = $db->saldoTerakhir();

        return view('pajak.ppn-keluaran.index', [
            'data' => $data,
            'saldo' => $saldo
        ]);

    }
}
