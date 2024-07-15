<?php

namespace App\Http\Controllers;

use App\Models\db\Barang\BarangStokHarga;
use App\Models\db\Barang\BarangUnit;
use App\Models\db\Konsumen;
use App\Models\db\Pajak;
use App\Models\transaksi\KeranjangJual;
use Illuminate\Http\Request;

class FormJualController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'barang_ppn' => 'required',
        ]);

        $units = BarangUnit::all();
        $keranjang = KeranjangJual::with(['barang.type.unit', 'barang.kategori'])->where('user_id', auth()->user()->id)->get();
        $dbPajak = new Pajak();
        $ppnRate = $dbPajak->where('untuk', 'ppn')->first()->persen;
        $pphRate = $dbPajak->where('untuk', 'pph')->first()->persen;
        $konsumen = Konsumen::where('active', 1)->get();


        return view('billing.form-jual.index', [
            'units' => $units,
            'keranjang' => $keranjang,
            'ppnRate' => $ppnRate,
            'pphRate' => $pphRate,
            'barang_ppn' => $data['barang_ppn'],
            'konsumen' => $konsumen,
        ]);
    }

    public function keranjang_store(Request $request)
    {
        $data = $request->validate([
            'barang_ppn' => 'required',
            'barang_id' => 'required',
            'jumlah' => 'required',
        ]);

        $data['jumlah'] = str_replace('.', '', $data['jumlah']);
        $data['harga_satuan'] = BarangStokHarga::where('barang_id', $data['barang_id'])->where('tipe', $data['barang_ppn'] == 1 ? 'ppn' : 'non-ppn')->first()->harga;
        $data['total'] = $data['jumlah'] * $data['harga_satuan'];
        $data['user_id'] = auth()->user()->id;

        KeranjangJual::create($data);

        return redirect()->back()->with('success', 'Barang berhasil ditambahkan ke keranjang');

    }

    public function keranjang_destroy(KeranjangJual $keranjangJual)
    {
        $keranjangJual->delete();

        return redirect()->back()->with('success', 'Barang berhasil dihapus dari keranjang');
    }

    public function keranjang_empty(Request $request)
    {
        KeranjangJual::where('user_id', auth()->user()->id)->where('barang_ppn', $request->barang_ppn)->delete();

        return redirect()->back()->with('success', 'Keranjang berhasil dikosongkan');
    }

    public function get_stok($id, $barangPpn)
    {
        $apa_ppn = $barangPpn == 1 ? 'ppn' : 'non-ppn';

        $data = BarangStokHarga::where('barang_id', $id)->where('tipe', $apa_ppn)->first();

        return response()->json($data);

    }
}
