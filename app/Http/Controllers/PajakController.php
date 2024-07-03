<?php

namespace App\Http\Controllers;

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
        $db = new InvoiceBelanja();

        $data = $db->ppn_masukan();
        $dataInven = InventarisInvoice::where('ppn', '>', 0)->where('lunas', 1)->get();
        // dd($data);
        return view('pajak.ppn-masukan.index', [
            'data' => $data,
            'dataInven' => $dataInven,
        ]);
    }
}
