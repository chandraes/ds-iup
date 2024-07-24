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
    public function keranjang_store(Request $request)
    {
        $data = $request->validate([
            'barang_stok_harga_id' => 'required|exists:barang_stok_hargas,id',
            'jumlah' => 'required',
            'barang_ppn' => 'required',
        ]);

        $product = BarangStokHarga::find($data['barang_stok_harga_id']);

        if($data['jumlah'] == 0 || $data['jumlah'] > $product->stok)
        {
            return redirect()->back()->with('error', 'Jumlah stok tidak mencukupi!');
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
        $productId = $request->input('product_id');
        $quantity = $request->input('quantity');

        $product = ProductJadi::find($productId);
        $cartItem = KeranjangJual::where('product_jadi_id', $productId)->first();

        if ($cartItem) {
            $newQuantity = $cartItem->jumlah + $quantity;
            if ($newQuantity > $product->stock_packaging) {
                return response()->json(['success' => false, 'message' => 'Jumlah item melebihi stok yang tersedia.']);
            }
            $cartItem->jumlah = $newQuantity;
            if ($cartItem->jumlah <= 0) {
                $cartItem->delete();
            } else {
                $cartItem->save();
            }
        } else {
            if ($quantity > $product->stock_packaging) {
                return response()->json(['success' => false, 'message' => 'Jumlah item melebihi stok yang tersedia.']);
            }
            KeranjangJual::create([
                'product_jadi_id' => $productId,
                'jumlah' => $quantity
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function keranjang_set(Request $request)
    {
        $productId = $request->input('product_id');
        $quantity = $request->input('quantity');

        $product = ProductJadi::find($productId);

        if ($quantity > $product->stock_packaging) {
            return response()->json(['success' => false, 'message' => 'Jumlah item melebihi stok yang tersedia.']);
        }

        $cartItem = KeranjangJual::where('product_jadi_id', $productId)->first();

        if ($cartItem) {
            $cartItem->jumlah = $quantity;
            if ($cartItem->jumlah <= 0) {
                $cartItem->delete();
            } else {
                $cartItem->save();
            }
        } else {
            KeranjangJual::create([
                'product_jadi_id' => $productId,
                'jumlah' => $quantity
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


}
