<?php

namespace App\Http\Controllers;

use App\Models\db\KategoriBahan;
use App\Models\db\KategoriProduct;
use App\Models\db\Kemasan;
use App\Models\db\Packaging;
use App\Models\db\Pajak;
use App\Models\db\Product;
use App\Models\db\Satuan;
use App\Models\db\Supplier;
use App\Models\transaksi\InvoiceBelanja;
use App\Models\transaksi\Keranjang;
use Illuminate\Http\Request;

class FormTransaksiController extends Controller
{
    public function index()
    {
        $hb = InvoiceBelanja::where('tempo', 1)->where('void', 0)->count();
        return view('billing.form-transaksi.index', [
            'hb' => $hb,
        ]);
    }

    public function hutang_belanja(Request $request)
    {
        $data = InvoiceBelanja::with(['supplier'])->where('tempo', 1)->where('void', 0)->get();
        // get unique supplier_id from $data
        $supplierIds = $data->pluck('supplier_id')->unique();

        $supplier = Supplier::where('status', 1)->whereIn('id', $supplierIds)->get();

        return view('billing.form-transaksi.hutang-belanja.index', [
            'data' => $data,
            'supplier' => $supplier
        ]);
    }

    public function hutang_belanja_bayar(InvoiceBelanja $invoice)
    {

        $db = new InvoiceBelanja();

        $store = $db->bayar_hutang($invoice);

        return redirect()->back()->with($store['status'], $store['message']);
    }

    public function bahan_baku_beli()
    {
        if (Supplier::where('status', 1)->count() == 0) {
            return redirect()->route('db.supplier')->with('error', 'Supplier belum ada, silahkan tambahkan supplier terlebih dahulu');
        }
        $supplier = Supplier::where('status', 1)->get();
        $kategori = KategoriBahan::all();
        $keranjang = Keranjang::with(['bahan_baku'])->where('user_id', auth()->id())->where('jenis', 1)->where('tempo', 0)->get();
        $satuan = Satuan::all();
        $ppn = Pajak::where('untuk', 'ppn')->first()->persen;

        return view('billing.form-transaksi.bahan-baku.beli', [
            'kategori' => $kategori,
            'keranjang' => $keranjang,
            'satuan' => $satuan,
            'supplier' => $supplier,
            'ppn' => $ppn
        ]);
    }

    public function keranjang_store(Request $request)
    {
        $data = $request->validate([
            'apa_konversi' => 'required',
            'bahan_baku_id' => 'required|exists:bahan_bakus,id',
            'jumlah' => 'required|numeric|min:1',
            'harga' => 'required',
            'satuan_id' => 'required_if:apa_konversi,==,0',
        ]);

        $data['user_id'] = auth()->user()->id;

        if($data['apa_konversi'] == 1){
            $data['satuan_id'] = 2;
        }

        $data['harga'] = str_replace('.', '', $data['harga']);
        $data['total'] = $data['jumlah'] * $data['harga'];

        unset($data['apa_konversi']);

        Keranjang::create($data);

        return redirect()->route('billing.form-transaksi.bahan-baku.beli')->with('success', 'Berhasil ditambahkan ke keranjang');
    }

    public function keranjang_delete(Keranjang $keranjang)
    {
        $keranjang->delete();

        return redirect()->back()->with('success', 'Berhasil dihapus dari keranjang');
    }

    public function keranjang_empty()
    {
        $count = Keranjang::where('user_id', auth()->id())->where('tempo', 0)->where('jenis', 1)->count();

        if ($count == 0) {
            return redirect()->back()->with('error', 'Keranjang kosong');
        }

        Keranjang::where('user_id', auth()->id())->where('jenis', 1)->where('tempo', 0)->delete();

        return redirect()->route('billing.form-transaksi.bahan-baku.beli')->with('success', 'Keranjang berhasil dikosongkan');
    }

    public function keranjang_checkout(Request $request)
    {
        ini_set('max_execution_time', 300); //300 seconds = 5 minutes
        ini_set('memory_limit', '512M');

        $data = $request->validate([
            'uraian' => 'required',
            'ppn' => 'required',
            'diskon' => 'required',
            'nama_rek' => 'required',
            'no_rek' => 'required',
            'bank' => 'required',
            'supplier_id' => 'required|exists:suppliers,id',
            'add_fee' => 'required',
        ]);

        $db = new Keranjang();

        if ($db->where('user_id', auth()->id())->where('tempo', 0)->where('jenis', 1)->count() == 0) {
            return redirect()->back()->with('error', 'Keranjang kosong');
        }

        $store = $db->checkout($data);

        return redirect()->back()->with($store['status'], $store['message']);
    }

    public function bahan_baku_beli_tempo()
    {
        if (Supplier::where('status', 1)->count() == 0) {
            return redirect()->route('db.supplier')->with('error', 'Supplier belum ada, silahkan tambahkan supplier terlebih dahulu');
        }
        $supplier = Supplier::where('status', 1)->get();
        $kategori = KategoriBahan::all();
        $keranjang = Keranjang::with(['bahan_baku'])->where('user_id', auth()->id())->where('jenis', 1)->where('tempo', 1)->get();
        $satuan = Satuan::all();
        $ppn = Pajak::where('untuk', 'ppn')->first()->persen;

        return view('billing.form-transaksi.bahan-baku.tempo.index', [
            'kategori' => $kategori,
            'keranjang' => $keranjang,
            'satuan' => $satuan,
            'supplier' => $supplier,
            'ppn' => $ppn
        ]);
    }

    public function keranjang_tempo_store(Request $request)
    {
        $data = $request->validate([
            'apa_konversi' => 'required',
            'bahan_baku_id' => 'required|exists:bahan_bakus,id',
            'jumlah' => 'required|numeric|min:1',
            'harga' => 'required',
            'satuan_id' => 'required_if:apa_konversi,==,0',
            'add_fee' => 'required'
        ]);

        $data['user_id'] = auth()->user()->id;

        if($data['apa_konversi'] == 1){
            $data['satuan_id'] = 2;
        }

        $data['tempo'] = 1;
        $data['harga'] = str_replace('.', '', $data['harga']);
        $data['total'] = $data['jumlah'] * $data['harga'];
        $data['add_fee'] = str_replace('.', '', $data['add_fee']);

        unset($data['apa_konversi']);

        Keranjang::create($data);

        return redirect()->back()->with('success', 'Berhasil ditambahkan ke keranjang');
    }

    public function keranjang_tempo_empty()
    {
        $count = Keranjang::where('user_id', auth()->id())->where('jenis', 1)->where('tempo', 1)->count();

        if ($count == 0) {
            return redirect()->back()->with('error', 'Keranjang kosong');
        }

        Keranjang::where('user_id', auth()->id())->where('jenis', 1)->where('tempo', 1)->delete();

        return redirect()->back()->with('success', 'Keranjang berhasil dikosongkan');
    }

    public function keranjang_tempo_checkout(Request $request)
    {
        ini_set('max_execution_time', 300); //300 seconds = 5 minutes
        ini_set('memory_limit', '512M');

        $data = $request->validate([
            'uraian' => 'required',
            'dp' => 'required',
            'jatuh_tempo' => 'required',
            'ppn' => 'required',
            'diskon' => 'required',
            'nama_rek' => 'required',
            'no_rek' => 'required',
            'bank' => 'required',
            'supplier_id' => 'required|exists:suppliers,id',
            'dp' => 'required',
            'dp_ppn' => 'required',
            'jatuh_tempo' => 'required',
        ]);

        $db = new Keranjang();
        $dp = str_replace('.', '', $data['dp']);

        if ($db->where('user_id', auth()->id())->where('jenis', 1)->where('tempo', 1)->count() == 0) {
            return redirect()->back()->with('error', 'Keranjang kosong');
        }

        $store = $db->checkoutTempo($data);

        return redirect()->back()->with($store['status'], $store['message']);
    }

    public function get_product(Request $request)
    {
        $data = Product::where('kategori_product_id', $request->kategori_product_id)->get();

        if($data->count() == 0){
            $data = 'empty';
            $result = [
                'status' => 0,
                'message' => 'Kategori belum memiliki product!'
            ];
        } else {
            $result = [
                'status' => 1,
                'message' => 'Produk ditemukan',
                'data' => $data
            ];
        }

        return response()->json($result);
    }

    public function get_kemasan(Request $request)
    {
        $data = Kemasan::with('satuan')->where('product_id', $request->product_id)->get();

        if($data->count() == 0){
            $data = 'empty';
            $result = [
                'status' => 0,
                'message' => 'Kemasan tidak ditemukan'
            ];
        } else {
            $result = [
                'status' => 1,
                'message' => 'Kemasan ditemukan',
                'data' => $data
            ];
        }

        return response()->json($result);
    }

    public function kemasan()
    {
        if (Supplier::where('status', 1)->count() == 0) {
            return redirect()->route('db.supplier')->with('error', 'Supplier belum ada, silahkan tambahkan supplier terlebih dahulu');
        }
        $supplier = Supplier::where('status', 1)->get();
        $kategori = KategoriProduct::all();
        $keranjang = Keranjang::with(['kemasan'])->where('user_id', auth()->id())->where('jenis', 2)->where('tempo', 0)->get();
        $satuan = Satuan::all();
        $ppn = Pajak::where('untuk', 'ppn')->first()->persen;

        return view('billing.form-transaksi.kemasan.index', [
            'kategori' => $kategori,
            'keranjang' => $keranjang,
            'satuan' => $satuan,
            'supplier' => $supplier,
            'ppn' => $ppn
        ]);
    }

    public function kemasan_tempo()
    {
        if (Supplier::where('status', 1)->count() == 0) {
            return redirect()->route('db.supplier')->with('error', 'Supplier belum ada, silahkan tambahkan supplier terlebih dahulu');
        }
        $supplier = Supplier::where('status', 1)->get();
        $kategori = KategoriProduct::all();
        $keranjang = Keranjang::with(['kemasan'])->where('user_id', auth()->id())->where('jenis', 2)->where('tempo', 1)->get();
        $satuan = Satuan::all();
        $ppn = Pajak::where('untuk', 'ppn')->first()->persen;

        return view('billing.form-transaksi.kemasan.tempo.index', [
            'kategori' => $kategori,
            'keranjang' => $keranjang,
            'satuan' => $satuan,
            'supplier' => $supplier,
            'ppn' => $ppn
        ]);
    }

    public function kemasan_keranjang_tempo_store(Request $request)
    {
        $data = $request->validate([
            'kemasan_id' => 'required|exists:kemasans,id',
            'jumlah' => 'required|numeric|min:1',
            'harga' => 'required',
            'satuan_id' => 'required_if:apa_konversi,==,0',
        ]);


        $data['satuan_id'] = Kemasan::find($data['kemasan_id'])->satuan_id;
        $data['user_id'] = auth()->user()->id;

        $data['harga'] = str_replace('.', '', $data['harga']);
        $data['jumlah'] = str_replace('.', '', $data['jumlah']);
        $data['total'] = $data['jumlah'] * $data['harga'];
        $data['jenis'] = 2;
        $data['tempo'] = 1;


        Keranjang::create($data);

        return redirect()->back()->with('success', 'Berhasil ditambahkan ke keranjang');
    }

    public function kemasan_keranjang_tempo_checkout(Request $request)
    {
        ini_set('max_execution_time', 300); //300 seconds = 5 minutes
        ini_set('memory_limit', '512M');

        $data = $request->validate([
            'uraian' => 'required',
            'ppn' => 'required',
            'diskon' => 'required',
            'nama_rek' => 'required',
            'no_rek' => 'required',
            'bank' => 'required',
            'supplier_id' => 'required|exists:suppliers,id',
            'dp' => 'required',
            'dp_ppn' => 'required',
            'jatuh_tempo' => 'required',
        ]);

        $db = new Keranjang();
        $dp = str_replace('.', '', $data['dp']);

        if ($db->where('user_id', auth()->id())->where('jenis', 2)->where('tempo', 1)->count() == 0) {
            return redirect()->back()->with('error', 'Keranjang kosong');
        }

        if($dp > $db->where('user_id', auth()->id())->where('jenis', 2)->where('tempo', 1)->sum('total')){
            return redirect()->back()->with('error', 'DP melebihi total tagihan');
        }

        $store = $db->checkoutKemasanTempo($data);

        return redirect()->back()->with($store['status'], $store['message']);
    }

    public function kemasan_keranjang_tempo_empty()
    {
        $count = Keranjang::where('user_id', auth()->id())->where('jenis', 2)->where('tempo', 1)->count();

        if ($count == 0) {
            return redirect()->back()->with('error', 'Keranjang kosong');
        }

        Keranjang::where('user_id', auth()->id())->where('jenis', 2)->where('tempo', 1)->delete();

        return redirect()->back()->with('success', 'Keranjang berhasil dikosongkan');
    }

    public function kemasan_store(Request $request)
    {
        $data = $request->validate([
            'kemasan_id' => 'required|exists:kemasans,id',
            'jumlah' => 'required|numeric|min:1',
            'harga' => 'required',
            'satuan_id' => 'required_if:apa_konversi,==,0',
        ]);


        $data['satuan_id'] = Kemasan::find($data['kemasan_id'])->satuan_id;
        $data['user_id'] = auth()->user()->id;

        $data['harga'] = str_replace('.', '', $data['harga']);
        $data['total'] = $data['jumlah'] * $data['harga'];
        $data['jenis'] = 2;


        Keranjang::create($data);

        return redirect()->route('billing.form-transaksi.kemasan')->with('success', 'Berhasil ditambahkan ke keranjang');
    }

    public function kemasan_keranjang_checkout(Request $request)
    {
        ini_set('max_execution_time', 300); //300 seconds = 5 minutes
        ini_set('memory_limit', '512M');

        $data = $request->validate([
            'uraian' => 'required',
            'ppn' => 'required',
            'diskon' => 'required',
            'nama_rek' => 'required',
            'no_rek' => 'required',
            'bank' => 'required',
            'supplier_id' => 'required|exists:suppliers,id',
            'add_fee' => 'required',
        ]);

        $db = new Keranjang();

        if ($db->where('user_id', auth()->id())->where('jenis', 2)->where('tempo', 0)->count() == 0) {
            return redirect()->back()->with('error', 'Keranjang kosong');
        }

        $store = $db->checkoutKemasan($data);

        return redirect()->back()->with($store['status'], $store['message']);
    }

    public function kemasan_keranjang_delete(Keranjang $keranjang)
    {
        $keranjang->delete();

        return redirect()->back()->with('success', 'Berhasil dihapus dari keranjang');
    }

    public function kemasan_keranjang_empty()
    {
        $count = Keranjang::where('user_id', auth()->id())->where('jenis', 2)->where('tempo', 0)->count();

        if ($count == 0) {
            return redirect()->back()->with('error', 'Keranjang kosong');
        }

        Keranjang::where('user_id', auth()->id())->where('jenis', 2)->where('tempo', 0)->delete();

        return redirect()->back()->with('success', 'Keranjang berhasil dikosongkan');
    }


    public function packaging()
    {
        if (Supplier::where('status', 1)->count() == 0) {
            return redirect()->route('db.supplier')->with('error', 'Supplier belum ada, silahkan tambahkan supplier terlebih dahulu');
        }
        $supplier = Supplier::where('status', 1)->get();
        $kategori = Packaging::all();
        $keranjang = Keranjang::with(['bahan_baku'])->where('user_id', auth()->id())->where('jenis', 3)->where('tempo', 0)->get();
        $satuan = Satuan::all();
        $ppn = Pajak::where('untuk', 'ppn')->first()->persen;

        return view('billing.form-transaksi.packaging.index', [
            'kategori' => $kategori,
            'keranjang' => $keranjang,
            'satuan' => $satuan,
            'supplier' => $supplier,
            'ppn' => $ppn
        ]);
    }

    public function packaging_store(Request $request)
    {
        $data = $request->validate([
            'packaging_id' => 'required|exists:packagings,id',
            'jumlah' => 'required|numeric|min:1',
            'harga' => 'required',
        ]);


        $data['satuan_id'] = Packaging::find($data['packaging_id'])->satuan_id;
        $data['user_id'] = auth()->user()->id;

        $data['harga'] = str_replace('.', '', $data['harga']);
        $data['total'] = $data['jumlah'] * $data['harga'];
        $data['jenis'] = 3;


        Keranjang::create($data);

        return redirect()->back()->with('success', 'Berhasil ditambahkan ke keranjang');
    }

    public function packaging_keranjang_delete(Keranjang $keranjang)
    {
        $keranjang->delete();

        return redirect()->back()->with('success', 'Berhasil dihapus dari keranjang');
    }

    public function packaging_keranjang_empty()
    {
        $count = Keranjang::where('user_id', auth()->id())->where('jenis', 3)->where('tempo', 0)->count();

        if ($count == 0) {
            return redirect()->back()->with('error', 'Keranjang kosong');
        }

        Keranjang::where('user_id', auth()->id())->where('jenis', 3)->where('tempo', 0)->delete();

        return redirect()->back()->with('success', 'Keranjang berhasil dikosongkan');
    }

    public function packaging_keranjang_checkout(Request $request)
    {
        ini_set('max_execution_time', 300); //300 seconds = 5 minutes
        ini_set('memory_limit', '512M');

        $data = $request->validate([
            'uraian' => 'required',
            'ppn' => 'required',
            'diskon' => 'required',
            'nama_rek' => 'required',
            'no_rek' => 'required',
            'bank' => 'required',
            'supplier_id' => 'required|exists:suppliers,id',
            'add_fee' => 'required',
        ]);

        $db = new Keranjang();

        if ($db->where('user_id', auth()->id())->where('jenis', 3)->where('tempo', 0)->count() == 0) {
            return redirect()->back()->with('error', 'Keranjang kosong');
        }

        $store = $db->checkoutPackaging($data);

        return redirect()->back()->with($store['status'], $store['message']);
    }

    public function packaging_tempo()
    {
        if (Supplier::where('status', 1)->count() == 0) {
            return redirect()->route('db.supplier')->with('error', 'Supplier belum ada, silahkan tambahkan supplier terlebih dahulu');
        }
        $supplier = Supplier::where('status', 1)->get();
        $kategori = Packaging::all();
        $keranjang = Keranjang::with(['packaging'])->where('user_id', auth()->id())->where('jenis', 3)->where('tempo', 1)->get();
        $satuan = Satuan::all();
        $ppn = Pajak::where('untuk', 'ppn')->first()->persen;

        return view('billing.form-transaksi.packaging.tempo.index', [
            'kategori' => $kategori,
            'keranjang' => $keranjang,
            'satuan' => $satuan,
            'supplier' => $supplier,
            'ppn' => $ppn
        ]);
    }

    public function packaging_keranjang_tempo_store(Request $request)
    {
        $data = $request->validate([
            'packaging_id' => 'required|exists:packagings,id',
            'jumlah' => 'required|numeric|min:1',
            'harga' => 'required',
            'add_fee' => 'required'
        ]);

        $data['satuan_id'] = Packaging::find($data['packaging_id'])->satuan_id;
        $data['user_id'] = auth()->user()->id;

        $data['harga'] = str_replace('.', '', $data['harga']);
        $data['total'] = $data['jumlah'] * $data['harga'];
        $data['add_fee'] = str_replace('.', '', $data['add_fee']);
        $data['jenis'] = 3;
        $data['tempo'] = 1;

        Keranjang::create($data);

        return redirect()->back()->with('success', 'Berhasil ditambahkan ke keranjang');
    }

    public function packaging_keranjang_tempo_empty()
    {
        $count = Keranjang::where('user_id', auth()->id())->where('jenis', 3)->where('tempo', 1)->count();

        if ($count == 0) {
            return redirect()->back()->with('error', 'Keranjang kosong');
        }

        Keranjang::where('user_id', auth()->id())->where('jenis', 3)->where('tempo', 1)->delete();

        return redirect()->back()->with('success', 'Keranjang berhasil dikosongkan');
    }

    public function packaging_keranjang_tempo_checkout(Request $request)
    {
        ini_set('max_execution_time', 300); //300 seconds = 5 minutes
        ini_set('memory_limit', '512M');

        $data = $request->validate([
            'uraian' => 'required',
            'ppn' => 'required',
            'diskon' => 'required',
            'nama_rek' => 'required',
            'no_rek' => 'required',
            'bank' => 'required',
            'supplier_id' => 'required|exists:suppliers,id',
            'dp' => 'required',
            'dp_ppn' => 'required',
            'jatuh_tempo' => 'required',
        ]);

        $db = new Keranjang();
        $dp = str_replace('.', '', $data['dp']);

        if ($db->where('user_id', auth()->id())->where('jenis', 3)->where('tempo', 1)->count() == 0) {
            return redirect()->back()->with('error', 'Keranjang kosong');
        }

        if($dp > $db->where('user_id', auth()->id())->where('jenis', 3)->where('tempo', 1)->sum('total')){
            return redirect()->back()->with('error', 'DP melebihi total tagihan');
        }

        $store = $db->checkoutPackagingTempo($data);

        return redirect()->back()->with($store['status'], $store['message']);
    }

    public function void(InvoiceBelanja $invoice)
    {
        $db = new InvoiceBelanja();

        $res = $db->void_belanja($invoice->id);

        return redirect()->back()->with($res['status'], $res['message']);
    }
}
