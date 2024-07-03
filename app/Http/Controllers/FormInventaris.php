<?php

namespace App\Http\Controllers;

use App\Models\db\InventarisJenis;
use App\Models\db\InventarisKategori;
use App\Models\db\InventarisRekap;
use App\Models\db\Pajak;
use App\Models\transaksi\InventarisInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormInventaris extends Controller
{
    public function index()
    {
        $ppn = Pajak::where('untuk', 'ppn')->first()->persen;
        $data = InventarisKategori::all();
        return view('billing.form-inventaris.beli', [
            'ppn' => $ppn,
            'data' => $data
        ]);
    }

    public function getJenis(Request $request)
    {
        $data = InventarisJenis::with('kategori')->where('kategori_id', $request->kategori_id)->get();
        $result = [
            'status' => $data->isEmpty() ? 0 : 1,
            'message' => $data->isEmpty() ? 'Data jenis inventaris tidak ditemukan' : 'Data jenis inventaris ditemukan',
            'data' => $data->isEmpty() ? null : $data
        ];

        return response()->json($result);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'pembayaran' => 'required',
            'inventaris_jenis_id' => 'required|exists:inventaris_jenis,id',
            'apa_ppn' => 'required',
            'uraian' => 'required',
            'jumlah' => 'required',
            'harga_satuan' => 'required',
            'ppn' => 'required',
            'nama_rek' => 'required',
            'no_rek' => 'required',
            'bank' => 'required',
            'add_fee' => 'required',
            'diskon' => 'required',
            'dp' => 'required_unless:pembayaran,1',
            'tanggal_jatuh_tempo' => 'required_unless:pembayaran,1',
        ]);

        $data['ppn'] = str_replace('.', '', $data['ppn']);
        $data['harga_satuan'] = str_replace('.', '', $data['harga_satuan']);
        $data['add_fee'] = str_replace('.', '', $data['add_fee']);
        $data['diskon'] = str_replace('.', '', $data['diskon']);
        $data['status'] = 'beli';
        $data['jenis'] = 1;

        $db = new InventarisInvoice();

        $res = $db->beliInventaris($data);

        return redirect()->back()->with($res['status'], $res['message']);

    }

    public function hutang()
    {
        $data = InventarisInvoice::where('pembayaran', 2)->where('lunas', 0)->get();

        return view('billing.form-inventaris.hutang', [
            'data' => $data
        ]);
    }

    public function pelunasan(InventarisInvoice $invoice)
    {
        $db = new InventarisInvoice();

        $res = $db->pelunasan($invoice->id);

        return redirect()->back()->with($res['status'], $res['message']);
    }
}
