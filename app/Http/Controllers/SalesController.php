<?php

namespace App\Http\Controllers;

use App\Models\db\Barang\Barang;
use App\Models\db\Barang\BarangKategori;
use App\Models\db\Barang\BarangNama;
use App\Models\db\Barang\BarangStokHarga;
use App\Models\db\Barang\BarangType;
use App\Models\db\Barang\BarangUnit;
use App\Models\db\Karyawan;
use App\Models\db\Konsumen;
use App\Models\db\Pajak;
use App\Models\Pengaturan;
use App\Models\transaksi\InvoiceJual;
use App\Models\transaksi\InvoiceJualSales;
use App\Models\transaksi\InvoiceJualSalesDetail;
use App\Models\transaksi\KeranjangInden;
use App\Models\transaksi\KeranjangJual;
use App\Models\transaksi\OrderInden;
use App\Models\transaksi\OrderIndenDetail;
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

        // $ppnValue = $data['barang_ppn'];
        // $oppositePpnValue = $ppnValue == 1 ? 0 : 1;

        // $checkKeranjang = KeranjangJual::where('barang_ppn', $oppositePpnValue)->where('user_id', auth()->user()->id)->first();
        // if ($checkKeranjang) {
        //     $errorMessage = $ppnValue == 1
        //         ? 'Keranjang sudah terisi dengan barang non ppn. Silahkan hapus barang non ppn terlebih dahulu'
        //         : 'Keranjang sudah terisi dengan barang ppn. Silahkan hapus barang ppn terlebih dahulu';

        //     return redirect()->back()->with('error', $errorMessage);
        // }

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

        $keranjang = KeranjangJual::with('stok', 'barang')->where('user_id', auth()->user()->id)->get();
        $dbPajak = new Pajak;
        $total = KeranjangJual::where('user_id', auth()->user()->id)->sum('total');
        $ppn = $dbPajak->where('untuk', 'ppn')->first()->persen;
        $konsumen = Konsumen::with('kode_toko')->where('active', 1)->where('karyawan_id', auth()->user()->karyawan_id)->get();
        $adaPpn = $keranjang->where('barang_ppn', 1)->count() > 0 ? 1 : 0;
        $penyesuaian = Pengaturan::where('untuk', 'penyesuaian_jual')->first()->nilai;
        Carbon::setLocale('id');

        $barang = Barang::with(['barang_nama', 'satuan'])->get();
        // Format the date
        $tanggal = Carbon::now()->translatedFormat('d F Y');
        $jam = Carbon::now()->translatedFormat('H:i');
        $orderInden = KeranjangInden::with(['barang.kategori', 'barang.barang_nama', 'barang.satuan'])->where('user_id', auth()->user()->id)->get();

        $db = new InvoiceJual();


        return view('sales.stok-harga.keranjang', [
            'orderInden' => $orderInden,
            'barang' => $barang,
            'keranjang' => $keranjang,
            'ppn' => $ppn,
            'total' => $total,
            'konsumen' => $konsumen,
            'tanggal' => $tanggal,
            'jam' => $jam,
            'penyesuaian' => $penyesuaian,
            'adaPpn' => $adaPpn
        ]);
    }

    public function keranjang_inden_store(Request $request)
    {
        $data = $request->validate([
            'barang_id' => 'required|exists:barangs,id',
            'jumlah' => 'required',
        ]);

        KeranjangInden::create([
            'user_id' => auth()->user()->id,
            'barang_id' => $data['barang_id'],
            'jumlah' => $data['jumlah'],
        ]);

        return redirect()->back()->with('success', 'Barang inden berhasil ditambahkan ke keranjang');
    }

    public function keranjang_inden_delete(KeranjangInden $keranjangInden)
    {
        $keranjangInden->delete();

        return response()->json(['success' => true]);
    }

    public function keranjang_delete(KeranjangJual $keranjang)
    {
        $keranjang->delete();

        return response()->json(['success' => true]);
    }

    public function keranjang_checkout(Request $request)
    {
        // Ambil data keranjang berdasarkan user
        $keranjang = KeranjangJual::where('user_id', auth()->user()->id)->get();

        // Validasi berdasarkan jenis barang (PPN atau Non-PPN)
        $isPpn = $keranjang->where('barang_ppn', 1)->isNotEmpty();
        $isNonPpn = $keranjang->where('barang_ppn', 0)->isNotEmpty();

        $rules = [
            'konsumen_id' => 'required',
            'pembayaran' => 'required',
            'nama' => 'required_if:konsumen_id,*',
            'no_hp' => 'required_if:konsumen_id,*',
            'npwp' => 'nullable',
            'alamat' => 'nullable',
        ];

        if ($isPpn) {
            $rules = array_merge($rules, [
                'diskon' => 'required',
                'add_fee' => 'required',
                'dp' => 'nullable',
                'dp_ppn' => 'nullable',
                'dipungut' => 'nullable',
            ]);
        }

        if ($isNonPpn) {
            $rules = array_merge($rules, [
                'diskon_non_ppn' => 'required',
                'add_fee_non_ppn' => 'required',
                'dp_non_ppn' => 'nullable',
            ]);
        }

        $data = $request->validate($rules);

        // Atur batas waktu eksekusi dan memori
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        // Validasi tambahan untuk konsumen cash
        if ($data['konsumen_id'] == '*' && $data['pembayaran'] != 1) {
            return redirect()->back()->with('error', 'Konsumen cash tidak bisa memilih sistem pembayaran lain selain cash');
        }

        // Proses checkout
        $db = new KeranjangJual;
        $res = $db->checkoutSales($data);

        if ($res['status'] == 'error') {
            return redirect()->back()->with('error', $res['message']);
        }

        return redirect()->route('sales.stok')->with($res['status'], $res['message']);
    }

    public function order(Request $request)
    {
        if (auth()->user()->karyawan_id == null) {
            return redirect()->back()->with('error', 'Akun belum memiliki Karyawan ID, Silahkan menghubungi Admin.');
        }

        $req = $request->validate([
            'kas_ppn' => 'required|boolean',
        ]);

        $data = InvoiceJualSales::where('karyawan_id', auth()->user()->karyawan_id)->where('kas_ppn', $req['kas_ppn'])->where('is_finished', 0)->get();
        $ppn = Pajak::where('untuk', 'ppn')->first()->persen;

        return view('sales.order.index', [
            'data' => $data,
            'ppn' => $ppn
        ]);
    }

    public function order_void(InvoiceJualSales $order)
    {
        $db = new InvoiceJualSales;
        $res = $db->order_void($order->id);

        return response()->json($res);
    }

    public function order_detail(InvoiceJualSales $order)
    {
        $ppn = Pajak::where('untuk', 'ppn')->first()->persen;
        $penyesuaian = Pengaturan::where('untuk', 'penyesuaian_jual')->first()->nilai;

        return view('sales.order.detail', [
            'order' => $order->load('konsumen', 'invoice_detail.barang', 'invoice_detail.barangStokHarga'),
            'ppn' => $ppn,
            'penyesuaian' => $penyesuaian,
        ]);
    }

    public function order_detail_delete(InvoiceJualSalesDetail $orderDetail)
    {
        $orderDetail->update([
            'deleted' => !$orderDetail->deleted,
        ]);

        return redirect()->back()->with('success', 'Item ditandai sebagai dihapus. Silahkan lanjutkan proses untuk menghapus item ini.');
    }

    public function order_detail_update(InvoiceJualSales $order, Request $request)
    {
        $data = $request->validate([
            'pembayaran' => 'required',
            'diskon' => 'required',
            'add_fee' => 'required',
            'dp' => 'nullable',
            'dp_ppn' => 'nullable',
            'dipungut' => 'nullable',
        ]);

        $data['id'] = $order->id;

        $db = new InvoiceJualSales;

        $res = $db->update_order($data);

        return redirect()->route('sales.order', ['kas_ppn' => $order->kas_ppn])->with($res['status'], $res['message']);
    }

    public function preorder(Request $request)
    {
        $data = OrderInden::with(['detail.barang.barang_nama', 'detail.barang.satuan', 'konsumen'])
                ->where('karyawan_id', auth()->user()->karyawan_id)->where('is_finished', 0)->get();

        return view('sales.pre-order.index', [
            'data' => $data,
        ]);
    }

    public function preorder_detail(OrderInden $preorder)
    {
        $order = $preorder->load(['detail.barang.barang_nama', 'detail.barang.satuan', 'konsumen.kode_toko']);

        return view('sales.pre-order.detail', [
            'order' => $order,
        ]);
    }

    public function preorder_detail_delete(OrderIndenDetail $orderDetail)
    {

        $orderDetail->update([
            'deleted' => !$orderDetail->deleted,
        ]);

        $message = $orderDetail->deleted
            ? 'Item ditandai sebagai dihapus. Silahkan lanjutkan proses untuk menghapus item ini.'
            : 'Item berhasil dibatalkan dari status dihapus.';
        return response()->json(['status' => 'success', 'message' => $message]);
    }

    public function preorder_detail_update(OrderInden $preorder)
    {
        $detail = $preorder->detail;

        $count = $detail->where('deleted', 1)->count();


        if ($count == 0) {
            return redirect()->back()->with('error', 'Tidak ada item yang ditandai untuk dihapus');
        }

        $db = new OrderInden;

        $res = $db->update_order($preorder->id);

        return redirect()->route('sales.pre-order')->with($res['status'], $res['message']);
    }

    public function preorder_void(OrderInden $preorder)
    {

        $db = new OrderInden;

        $res = $db->order_void($preorder->id);

        return response()->json($res);

    }

    public function omset_harian(Request $request)
    {
        $month = $request->input('month') ?? date('m');
        $year = $request->input('year') ?? date('Y');

        $db = new InvoiceJual;

        $dataTahun = $db->dataTahun();

        // create array of month in indonesian with key 1-12
        $dataBulan = [
            '1' => 'Januari',
            '2' => 'Februari',
            '3' => 'Maret',
            '4' => 'April',
            '5' => 'Mei',
            '6' => 'Juni',
            '7' => 'Juli',
            '8' => 'Agustus',
            '9' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember'
        ];

        $karyawan = auth()->user()->karyawan_id;

        if ($karyawan == null) {
            return redirect()->back()->with('error', 'Akun belum memiliki Karyawan ID, Silahkan menghubungi Admin.');
        }

        $data = $db->omset_harian($month, $year, $karyawan);

        return view('sales.omset-harian.index', [
             'rows' => $data['data'],
            'karyawans' => $data['karyawans'],
            'dataTahun' => $dataTahun,
            'dataBulan' => $dataBulan,
        ]);
    }

    public function omset_harian_detail(Request $request)
    {
        $req = $request->validate([
            'tanggal' => 'required|date',
            'karyawan_id' => 'required|exists:karyawans,id',
        ]);

        if ($req['karyawan_id'] != auth()->user()->karyawan_id) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk melihat data ini');
        }

        $db = new InvoiceJual;

        $karyawan = Karyawan::where('id',$req['karyawan_id'])->select('nama')->first();

        if ($karyawan == null) {
            return redirect()->back()->with('error', 'Karyawan tidak ditemukan');
        }

        $data = $db->omset_harian_detail($request->input('tanggal'), $request->input('karyawan_id'));

        return view('sales.omset-harian.detail', [
            'data' => $data,
            'karyawan' => $karyawan,
        ]);
    }


    public function check_konsumen()
    {
        $konsumen = Konsumen::with(['kode_toko'])->where('active', 1)->where('karyawan_id', auth()->user()->karyawan_id)->get();

        if ($konsumen->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada konsumen yang ditemukan');
        }

        return view('sales.check-konsumen.index', [
            'konsumen' => $konsumen,
        ]);
    }

    public function check_konsumen_invoice(Request $request)
    {
        $req = $request->validate([
            'konsumen_id' => 'required|exists:konsumens,id',
        ]);

        $konsumen = Konsumen::find($req['konsumen_id']);

        if ($konsumen == null) {
            return response()->json(['success' => false, 'message' => 'Konsumen tidak ditemukan'], 404);
        }

        $db = new InvoiceJual;
        $data = InvoiceJual::where('konsumen_id', $konsumen->id)
                    ->where('titipan', 0)
                    ->where('lunas', 0)
                    ->where('void', 0)
                    ->where('jatuh_tempo', '<', today())
                    ->get();

        // Return JSON for AJAX
        return response()->json([
            'success' => true,
            'total' => $data->count(),
            'data' => $data,
            'konsumen' => $konsumen,
        ]);
    }


}
