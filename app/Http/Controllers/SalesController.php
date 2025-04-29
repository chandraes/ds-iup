<?php

namespace App\Http\Controllers;

use App\Models\db\Barang\BarangKategori;
use App\Models\db\Barang\BarangNama;
use App\Models\db\Barang\BarangStokHarga;
use App\Models\db\Barang\BarangType;
use App\Models\db\Barang\BarangUnit;
use App\Models\db\Konsumen;
use App\Models\db\Pajak;
use App\Models\transaksi\InvoiceJual;
use App\Models\transaksi\KeranjangJual;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    public function stok(Request $request)
    {
        $ppnRate = Pajak::where('untuk', 'ppn')->first()->persen;

        $unitFilter = $request->input('unit');
        $typeFilter = $request->input('type');
        $kategoriFilter = $request->input('kategori');
        $barangNamaFilter = $request->input('barang_nama');

        if (! empty($unitFilter) && $unitFilter != '') {
            $selectType = BarangType::where('barang_unit_id', $unitFilter)->get();

            $selectKategori = BarangKategori::whereHas('barangs', function ($query) use ($unitFilter) {
                $query->whereHas('type', function ($query) use ($unitFilter) {
                    $query->where('barang_unit_id', $unitFilter);
                });
            })->get();

            $selectBarangNama = BarangNama::whereHas('barang', function ($query) use ($unitFilter) {
                $query->whereHas('type', function ($query) use ($unitFilter) {
                    $query->where('barang_unit_id', $unitFilter);
                });
            })->get();

        } else {
            $selectType = BarangType::all();
            $selectKategori = BarangKategori::all();
            $selectBarangNama = BarangNama::select('id', 'nama')->distinct()->orderBy('id')->get();
        }

        $db = new BarangStokHarga;

        $jenis = 1;

        $data = $db->barangStok($jenis, $unitFilter, $typeFilter, $kategoriFilter, $barangNamaFilter);
        $nonPpn = $db->barangStok(2, $unitFilter, $typeFilter, $kategoriFilter, $barangNamaFilter);
        $units = BarangUnit::all();
        // $nonPpn = $db->barangStok(2, $unitFilter, $typeFilter, $kategoriFilter);

        $keranjang = KeranjangJual::where('user_id', auth()->user()->id)->get();

        // dd($units->toArray());
        return view('sales.stok-harga.index', [
            'data' => $data,
            'nonPpn' => $nonPpn,
            // 'kategori' => $kategori,
            'units' => $units,
            // 'type' => $type,
            'unitFilter' => $unitFilter,
            'typeFilter' => $typeFilter,
            'kategoriFilter' => $kategoriFilter,
            'selectType' => $selectType,
            'selectKategori' => $selectKategori,
            'ppnRate' => $ppnRate,
            'barangNamaFilter' => $barangNamaFilter,
            'selectBarangNama' => $selectBarangNama,
            'keranjang' => $keranjang,
        ]);
    }

    public function keranjang_store(Request $request)
    {
        $data = $request->validate([
            'barang_stok_harga_id' => 'required|exists:barang_stok_hargas,id',
            'jumlah' => 'required',
            'barang_ppn' => 'required',
        ]);

        $product = BarangStokHarga::find($data['barang_stok_harga_id']);

        if ($product->min_jual == null) {
            return redirect()->back()->with('error', 'Barang tidak memiliki aturan minimal jual! Silahkan hubungi admin');
        }

        $minJual = $product->min_jual;

        if ($data['jumlah'] % $minJual != 0) {
            return redirect()->back()->with('error', 'Jumlah barang harus kelipatan dari '.$minJual.'!');
        }

        if ($data['jumlah'] == 0 || $data['jumlah'] > $product->stok || $product->harga == 0) {
            $errorMessage = $data['jumlah'] == 0 || $data['jumlah'] > $product->stok
                ? 'Jumlah stok tidak mencukupi!'
                : 'Harga jual barang belum diatur!';

            return redirect()->back()->with('error', $errorMessage);
        }

        $ppnValue = $data['barang_ppn'];
        $oppositePpnValue = $ppnValue == 1 ? 0 : 1;

        $checkKeranjang = KeranjangJual::where('barang_ppn', $oppositePpnValue)->where('user_id', auth()->user()->id)->first();
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
                'total' => $quantity * $product->harga,
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
                'total' => $quantity * $product->harga,
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

        $keranjang = KeranjangJual::with('stok')->where('user_id', auth()->user()->id)->get();
        $dbPajak = new Pajak;
        $total = KeranjangJual::where('user_id', auth()->user()->id)->sum('total');
        $ppn = $dbPajak->where('untuk', 'ppn')->first()->persen;
        $nominalPpn = KeranjangJual::where('user_id', auth()->user()->id)->where('barang_ppn', 1)->first() ? ($total * $ppn / 100) : 0;
        $pphVal = $dbPajak->where('untuk', 'pph')->first()->persen;
        $konsumen = Konsumen::where('active', 1)->where('karyawan_id', auth()->user()->id)->get();
        $ppnStore = $nominalPpn > 0 ? 1 : 0;
        Carbon::setLocale('id');

        // Format the date
        $tanggal = Carbon::now()->translatedFormat('d F Y');
        $jam = Carbon::now()->translatedFormat('H:i');

        $db = new InvoiceJual();

        $kode = $db->generateKode($keranjang->first()->barang_ppn);

        return view('sales.stok-harga.keranjang', [
            'keranjang' => $keranjang,
            'ppn' => $ppn,
            'total' => $total,
            'pphVal' => $pphVal,
            'nominalPpn' => $nominalPpn,
            'konsumen' => $konsumen,
            'tanggal' => $tanggal,
            'jam' => $jam,
            'kode' => $kode,
            'ppnStore' => $ppnStore,
        ]);
    }

    public function keranjang_checkout(Request $request)
    {
        $data = $request->validate([
            'konsumen_id' => 'required',
            'pembayaran' => 'required',
            'diskon' => 'required',
            'add_fee' => 'required',
            'nama' => 'required_if:konsumen_id,*',
            'no_hp' => 'required_if:konsumen_id,*',
            'npwp' => 'nullable',
            'alamat' => 'nullable',
            'dp' => 'nullable',
            'dp_ppn' => 'nullable',
            'dipungut' => 'nullable',
        ]);

        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        if ($data['konsumen_id'] == '*' && $data['pembayaran'] != 1) {
            return redirect()->back()->with('error', 'Konsumen cash tidak bisa memilih sistem pembayaran lain selain cash');
        }

        $db = new KeranjangJual;

        $res = $db->checkout($data);

        return redirect()->route('billing.lihat-stok')->with($res['status'], $res['message']);
    }



}
