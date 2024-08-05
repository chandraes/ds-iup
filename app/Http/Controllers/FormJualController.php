<?php

namespace App\Http\Controllers;

use App\Models\db\Barang\BarangStokHarga;
use App\Models\db\Barang\BarangUnit;
use App\Models\db\Konsumen;
use App\Models\db\Pajak;
use App\Models\transaksi\Keranjang;
use App\Models\transaksi\KeranjangJual;
use Illuminate\Http\Request;

class FormJualController extends Controller
{

    public function get_konsumen(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|exists:konsumens,id'
        ]);

        $konsumen = Konsumen::find($data['id']);

        return response()->json($konsumen);
    }

    public function keranjang_store(Request $request)
    {
        $data = $request->validate([
            'barang_stok_harga_id' => 'required|exists:barang_stok_hargas,id',
            'jumlah' => 'required',
            'barang_ppn' => 'required',
        ]);

        $product = BarangStokHarga::find($data['barang_stok_harga_id']);

        if ($data['jumlah'] == 0 || $data['jumlah'] > $product->stok || $product->harga == 0) {
            $errorMessage = $data['jumlah'] == 0 || $data['jumlah'] > $product->stok
                ? 'Jumlah stok tidak mencukupi!'
                : 'Harga jual barang belum diatur!';
            return redirect()->back()->with('error', $errorMessage);
        }

        $ppnValue = $data['barang_ppn'];
        $oppositePpnValue = $ppnValue == 1 ? 0 : 1;

        $checkKeranjang = KeranjangJual::where('barang_ppn', $oppositePpnValue)->first();
        if ($checkKeranjang) {
            $errorMessage = $ppnValue == 1
                ? 'Keranjang sudah terisi dengan barang non ppn. Silahkan hapus barang non ppn terlebih dahulu'
                : 'Keranjang sudah terisi dengan barang ppn. Silahkan hapus barang ppn terlebih dahulu';
            return redirect()->back()->with('error', $errorMessage);
        }



        $data['user_id'] = auth()->user()->id;
        $data['jumlah'] = str_replace('.', '', $data['jumlah']);
        $data['barang_id'] = $product->barang_id;
        $data['harga_satuan'] = $product->harga;
        $data['total'] = $data['jumlah'] * $data['harga_satuan'];

        KeranjangJual::create($data);

        return redirect()->back()->with('success', 'Barang berhasil ditambahkan ke keranjang');
    }

    public function keranjang_update(Request $request)
    {
        $productId = $request->input('barang_stok_harga_id');
        $quantity = $request->input('quantity');

        $product = BarangStokHarga::find($productId);
        $cartItem = KeranjangJual::where('barang_stok_harga_id', $productId)->first();

        if ($cartItem) {
            $newQuantity = $cartItem->jumlah + $quantity;
            if ($newQuantity > $product->stok) {
                return response()->json(['success' => false, 'message' => 'Jumlah item melebihi stok yang tersedia.']);
            }
            $cartItem->jumlah = $newQuantity;
            if ($cartItem->jumlah <= 0) {
                $cartItem->delete();
            } else {
                $cartItem->save();
            }
        } else {
            if ($quantity > $product->stok) {
                return response()->json(['success' => false, 'message' => 'Jumlah item melebihi stok yang tersedia.']);
            }
            KeranjangJual::create([
                'user_id' => auth()->user()->id,
                'barang_ppn' => $product->barang->jenis == 1 ? 1 : 0,
                'barang_id' => $product->barang_id,
                'barang_stok_harga_id' => $productId,
                'jumlah' => $quantity,
                'harga_satuan' => $product->harga,
                'total' => $quantity * $product->harga
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function keranjang_set(Request $request)
    {
        $productId = $request->input('barang_stok_harga_id');
        $quantity = $request->input('quantity');

        $product = BarangStokHarga::find($productId);

        if ($quantity > $product->stok) {
            return response()->json(['success' => false, 'message' => 'Jumlah item melebihi stok yang tersedia.']);
        }

        $cartItem = KeranjangJual::where('barang_stok_harga_id', $productId)->first();

        if ($cartItem) {
            $cartItem->jumlah = $quantity;
            if ($cartItem->jumlah <= 0) {
                $cartItem->delete();
            } else {
                $cartItem->save();
            }
        } else {
            KeranjangJual::create([
                'user_id' => auth()->user()->id,
                'barang_ppn' => $product->barang->jenis == 1 ? 1 : 0,
                'barang_id' => $product->barang_id,
                'barang_stok_harga_id' => $productId,
                'jumlah' => $quantity,
                'harga_satuan' => $product->harga,
                'total' => $quantity * $product->harga
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function keranjang_empty()
    {
        $keranjang = KeranjangJual::where('user_id', auth()->user()->id)->get();

        if ($keranjang->isEmpty()) {
            return redirect()->back()->with('error', 'Keranjang sudah kosong');
        }

        KeranjangJual::where('user_id', auth()->user()->id)->delete();


        return redirect()->back()->with('success', 'Keranjang berhasil dikosongkan');
    }

    public function keranjang()
    {

        $keranjang = KeranjangJual::where('user_id', auth()->user()->id)->get();
        $dbPajak = new Pajak();
        $total = KeranjangJual::where('user_id', auth()->user()->id)->sum('total');
        $ppn = $dbPajak->where('untuk', 'ppn')->first()->persen;
        $nominalPpn = KeranjangJual::where('user_id', auth()->user()->id)->where('barang_ppn', 1)->first() ? ($total * $ppn / 100) : 0;
        $pphVal = $dbPajak->where('untuk', 'pph')->first()->persen;
        $konsumen = Konsumen::where('active', 1)->get();

        return view('billing.stok.keranjang-jual', [
            'keranjang' => $keranjang,
            'ppn' => $ppn,
            'total' => $total,
            'pphVal' => $pphVal,
            'nominalPpn' => $nominalPpn,
            'konsumen' => $konsumen
        ]);
    }

    public function keranjang_checkout(Request $request)
    {
        $data = $request->validate([
            'apa_pph' => 'required',
            'konsumen_id' => 'required',
            'nama' => 'required_if:konsumen_id,*',
            'no_hp' => 'nullable',
            'npwp' => 'nullable',
            'alamat' => 'nullable',
            'dp' => 'nullable',
        ]);

        $db = new KeranjangJual();

        $res = $db->checkout($data);

        return redirect()->route('billing.lihat-stok')->with($res['status'], $res['message']);
    }


}
