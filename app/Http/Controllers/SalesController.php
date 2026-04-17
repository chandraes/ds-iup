<?php

namespace App\Http\Controllers;

use App\Models\db\Barang\Barang;
use App\Models\db\Barang\BarangGrosir;
use App\Models\db\Barang\BarangKategori;
use App\Models\db\Barang\BarangNama;
use App\Models\db\Barang\BarangStokHarga;
use App\Models\db\Barang\BarangType;
use App\Models\db\Barang\BarangUnit;
use App\Models\db\DiskonUmum;
use App\Models\db\Karyawan;
use App\Models\db\KodeToko;
use App\Models\db\Konsumen;
use App\Models\db\Pajak;
use App\Models\db\Satuan;
use App\Models\Pengaturan;
use App\Models\transaksi\InvoiceJual;
use App\Models\transaksi\InvoiceJualSales;
use App\Models\transaksi\InvoiceJualSalesDetail;
use App\Models\transaksi\KeranjangInden;
use App\Models\transaksi\KeranjangJual;
use App\Models\transaksi\KeranjangJualKonsumen;
use App\Models\transaksi\OrderInden;
use App\Models\transaksi\OrderIndenDetail;
use App\Models\Wilayah;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Traits\Terbilang;

class SalesController extends Controller
{
    use Terbilang;

    public function barang_get_grosir(Request $request)
    {
        $data = $request->validate([
            'barang_id' => 'required|exists:barangs,id',
        ]);

        $grosir = BarangGrosir::with(['barang.satuan','satuan'])->where('barang_id',$data['barang_id'])->orderBy('qty_grosir')->get();
  // distinc satuan_id
        $satuanIds = $grosir->pluck('satuan_id')->unique();
        $satuans = Satuan::whereIn('id', $satuanIds)->get();

        return response()->json([
            'status' => 'success',
            'data' => $grosir,
            'satuans' => $satuans,
        ]);
    }

    public function jual()
    {
        $data = KeranjangJualKonsumen::with('konsumen.kode_toko')->where('user_id', Auth::user()->id)->get();

        return view('sales.jual.index', [
            'data' => $data,
        ]);
    }

    public function jual_store(Request $request)
    {
        $data = $request->validate([
            'konsumen_id' => 'required|exists:konsumens,id',
            'pembayaran' => 'required|in:1,2,3',
        ]);

        $data['user_id'] = Auth::user()->id;

        // Cek apakah konsumen sudah ada di keranjang

        $keranjang = KeranjangJualKonsumen::where('user_id', $data['user_id'])
            ->where('konsumen_id', $data['konsumen_id'])
            ->first();

        if ($keranjang) {
            return redirect()->back()->with('error', 'Konsumen sudah ada di keranjang');
        }

        try {
            $store = KeranjangJualKonsumen::create($data);
            return redirect()->route('sales.jual.keranjang', ['keranjang' => $store->id]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan konsumen ke keranjang: ' . $e->getMessage());
        }
    }

    public function jual_keranjang_empty(KeranjangJualKonsumen $keranjang)
    {
        try {
            $keranjang->keranjang_jual()->delete();
            // $keranjang->delete();
            return redirect()->back()->with('success', 'Keranjang berhasil dikosongkan');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengosongkan keranjang: ' . $e->getMessage());
        }
    }

    public function jual_keranjang(KeranjangJualKonsumen $keranjang, Request $request)
    {
        $keranjang->load('konsumen.kode_toko', 'user');

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
        $id = $keranjang->id;
        $keranjang = $keranjang->keranjang_jual;

        return view('sales.jual.pilih-barang', [
            'id' => $id,
            'keranjang' => $keranjang,
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
        ]);
    }

    public function jual_keranjang_delete(KeranjangJualKonsumen $keranjang)
    {
        try {
            $keranjang->delete();
            return redirect()->back()->with('success', 'Konsumen berhasil dihapus dari keranjang');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus konsumen dari keranjang: ' . $e->getMessage());
        }
    }

    public function jual_keranjang_grosir_store(Request $request)
    {
        $data = $request->validate([
            'keranjang_jual_konsumen_id' => 'required|exists:keranjang_jual_konsumens,id',
            'barang_stok_harga_id' => 'required|exists:barang_stok_hargas,id',
            'jumlah_grosir' => 'required|integer|min:1',
            'barang_ppn' => 'required',
            'satuan_grosir_id' => 'required|exists:satuans,id',
        ]);

        $data['is_grosir'] = 1;

        $barang_id = BarangStokHarga::find($data['barang_stok_harga_id'])->barang_id;

        $min_qty_barang = BarangGrosir::where('barang_id', $barang_id)
            ->where('satuan_id', $data['satuan_grosir_id'])
            ->min('qty_grosir');

        $data['jumlah'] = $data['jumlah_grosir'] * $min_qty_barang;



        $info = KeranjangJualKonsumen::with('konsumen')->where('id',$data['keranjang_jual_konsumen_id'])->first();

        if ($info->user_id != Auth::user()->id) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke keranjang ini');
        }

        // start Get data diskon

        $diskon_pembayaran = DiskonUmum::where('kode', $info->pembayaran)->first()->persen ?? 0;
        $diskon_konsumen = $info->konsumen->diskon_khusus ?? 0;

        $diskon_barang = Barang::where('id', $barang_id)
                            ->whereNotNull('diskon_mulai')
                            ->whereNotNull('diskon_selesai')
                            ->whereDate('diskon_mulai', '<=', now())
                            ->whereDate('diskon_selesai', '>=', now())
                            ->value('diskon') ?? 0;

        $diskon_grosir = BarangGrosir::where('barang_id', $barang_id)
                    ->where('qty_grosir', '<=', $data['jumlah'])
                    ->orderBy('qty_grosir')
                    ->get();

        // end Get data diskon

        $ppnRate = Pajak::where('untuk', 'ppn')->first()->persen;

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

        $data['jumlah'] = str_replace('.', '', $data['jumlah']);
        $data['barang_id'] = $product->barang_id;
        $data['harga_satuan'] = $product->harga;

        $harga_awal = $data['harga_satuan'];
        $total_diskon = 0;
        // Diskon Pembayaran
        if ($diskon_pembayaran > 0) {
            $total_diskon += (($diskon_pembayaran * $harga_awal) / 100);
            $harga_awal = ($harga_awal - (($diskon_pembayaran * $harga_awal)) / 100);
        }

        // diskon konsumen
        if ($diskon_konsumen > 0) {
            $total_diskon += (($diskon_konsumen * $harga_awal) / 100);
            $harga_awal = ($harga_awal - (($diskon_konsumen * $harga_awal)) / 100);
        }

        if ($diskon_barang > 0) {
            $total_diskon += (($diskon_barang * $harga_awal) / 100);
            $harga_awal = ($harga_awal - (($diskon_barang * $harga_awal)) / 100);
        }

        if ($diskon_grosir->count() > 0) {
            foreach ($diskon_grosir as $d) {
                $total_diskon += (($d->diskon * $harga_awal) / 100);
                $harga_awal = ($harga_awal - (($d->diskon * $harga_awal)) / 100);
            }
        }

        $data['diskon'] = floor($total_diskon);

        $harga_dpp_diskon = $data['harga_satuan'] - $data['diskon'];

        $data['ppn'] = $data['barang_ppn'] == 1 ? floor(($ppnRate * $harga_dpp_diskon) / 100) : 0;
        $data['harga_satuan_akhir'] = $data['harga_satuan'] - $data['diskon'] + $data['ppn'];
        $data['total_ppn'] = $data['ppn'] * $data['jumlah'];
        $data['total_diskon'] = $data['diskon'] * $data['jumlah'];
        // dd($data['diskon'], $data['total_diskon']);
        $data['total'] = $data['jumlah'] * $data['harga_satuan_akhir'];

        KeranjangJual::create($data);

        return redirect()->back()->with('success', 'Barang berhasil ditambahkan ke keranjang');


    }

    public function jual_keranjang_review(KeranjangJualKonsumen $keranjang)
    {
        $info = $keranjang;

        $dbPajak = new Pajak;
        $keranjang = $keranjang->keranjang_jual;
        $total = $keranjang->sum('total');
        $konsumen = $info->konsumen->load('kas');
        $adaPpn = $keranjang->where('barang_ppn', 1)->count() > 0 ? 1 : 0;
        $ppn = $dbPajak->where('untuk', 'ppn')->first()->persen;
        $penyesuaian = Pengaturan::where('untuk', 'penyesuaian_jual')->first()->nilai;

        $infoPlafon = [
            'total_plafon' => $konsumen->plafon,
            'total_tagihan' => $konsumen->sisaHutang(),
            'sisa_plafon' => $konsumen->plafon - $konsumen->sisaHutang(),
        ];

        // dd($infoPlafon);
        Carbon::setLocale('id');

        $barang = Barang::with(['barang_nama', 'satuan'])->get();
        // Format the date
        $tanggal = Carbon::now()->translatedFormat('d F Y');
        $jam = Carbon::now()->translatedFormat('H:i');
        $orderInden = KeranjangInden::with(['barang.kategori', 'barang.barang_nama', 'barang.satuan'])->where('user_id', Auth::user()->id)->get();

        return view('sales.jual.keranjang', [
            'info' => $info,
            'orderInden' => $orderInden,
            'penyesuaian' => $penyesuaian,
            'barang' => $barang,
            'keranjang' => $keranjang,
            'total' => $total,
            'ppn' => $ppn,
            'konsumen' => $konsumen,
            'tanggal' => $tanggal,
            'jam' => $jam,
            'adaPpn' => $adaPpn,
            'infoPlafon' => $infoPlafon,
        ]);
    }

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

        $keranjang = KeranjangJual::where('user_id', Auth::user()->id)->get();

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
            'keranjang_jual_konsumen_id' => 'required|exists:keranjang_jual_konsumens,id',
            'barang_stok_harga_id' => 'required|exists:barang_stok_hargas,id',
            'jumlah' => 'required',
            'barang_ppn' => 'required',
        ]);

        $info = KeranjangJualKonsumen::with('konsumen')->where('id',$data['keranjang_jual_konsumen_id'])->first();

        if ($info->user_id != Auth::user()->id) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke keranjang ini');
        }


        $product = BarangStokHarga::find($data['barang_stok_harga_id']);

        $diskon_pembayaran = DiskonUmum::where('kode', $info->pembayaran)->first()->persen ?? 0;
        $diskon_konsumen = $info->konsumen->diskon_khusus ?? 0;

        $diskon_barang = Barang::where('id', $product->barang_id)
                            ->whereNotNull('diskon_mulai')
                            ->whereNotNull('diskon_selesai')
                            ->whereDate('diskon_mulai', '<=', now())
                            ->whereDate('diskon_selesai', '>=', now())
                            ->value('diskon') ?? 0;

        $ppnRate = Pajak::where('untuk', 'ppn')->first()->persen;

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

        $data['jumlah'] = str_replace('.', '', $data['jumlah']);
        $data['barang_id'] = $product->barang_id;
        $data['harga_satuan'] = $product->harga;

        $harga_awal = $data['harga_satuan'];
        $total_diskon = 0;
        // Diskon Pembayaran
        if ($diskon_pembayaran > 0) {
            $total_diskon += (($diskon_pembayaran * $harga_awal) / 100);
            $harga_awal = ($harga_awal - (($diskon_pembayaran * $harga_awal)) / 100);
        }

        // diskon konsumen
        if ($diskon_konsumen > 0) {
            $total_diskon += (($diskon_konsumen * $harga_awal) / 100);
            $harga_awal = ($harga_awal - (($diskon_konsumen * $harga_awal)) / 100);
        }

        if ($diskon_barang > 0) {
            $total_diskon += (($diskon_barang * $harga_awal) / 100);
            $harga_awal = ($harga_awal - (($diskon_barang * $harga_awal)) / 100);
        }

        $data['diskon'] = floor($total_diskon);
        $harga_dpp_diskon = $data['harga_satuan'] - $data['diskon'];

        $data['ppn'] = $data['barang_ppn'] == 1 ? floor(($ppnRate * $harga_dpp_diskon) / 100) : 0;
        $data['harga_satuan_akhir'] = $data['harga_satuan'] - $data['diskon'] + $data['ppn'];
        $data['total_ppn'] = $data['ppn'] * $data['jumlah'];
        $data['total_diskon'] = $data['diskon'] * $data['jumlah'];

        $data['total'] = $data['jumlah'] * $data['harga_satuan_akhir'];

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
                'user_id' => Auth::user()->id,
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
                'user_id' => Auth::user()->id,
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
        $keranjang = KeranjangJual::where('user_id', Auth::user()->id)->get();

        if ($keranjang->isEmpty()) {
            return redirect()->back()->with('error', 'Keranjang sudah kosong');
        }

        KeranjangJual::where('user_id', Auth::user()->id)->delete();

        return redirect()->back()->with('success', 'Keranjang berhasil dikosongkan');
    }

    public function keranjang()
    {

        $keranjang = KeranjangJual::with('stok', 'barang')->where('user_id', Auth::user()->id)->get();
        $dbPajak = new Pajak;
        $total = KeranjangJual::where('user_id', Auth::user()->id)->sum('total');
        $ppn = $dbPajak->where('untuk', 'ppn')->first()->persen;
        $konsumen = Konsumen::with('kode_toko')->where('active', 1)->where('karyawan_id', Auth::user()->karyawan_id)->get();
        $adaPpn = $keranjang->where('barang_ppn', 1)->count() > 0 ? 1 : 0;
        $penyesuaian = Pengaturan::where('untuk', 'penyesuaian_jual')->first()->nilai;
        Carbon::setLocale('id');

        $barang = Barang::with(['barang_nama', 'satuan'])->get();
        // Format the date
        $tanggal = Carbon::now()->translatedFormat('d F Y');
        $jam = Carbon::now()->translatedFormat('H:i');
        $orderInden = KeranjangInden::with(['barang.kategori', 'barang.barang_nama', 'barang.satuan'])->where('user_id', Auth::user()->id)->get();

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
            'user_id' => Auth::user()->id,
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
        $rules = [
            // 'konsumen_id' => 'required',
            // 'pembayaran' => 'required',
            // 'nama' => 'required_if:konsumen_id,*',
            // 'no_hp' => 'required_if:konsumen_id,*',
            // 'npwp' => 'nullable',
            // 'alamat' => 'nullable',
            'keranjang_jual_konsumen_id' => 'required|exists:keranjang_jual_konsumens,id',
            'dp' => 'nullable',
            'dp_ppn' => 'nullable',
            'dipungut' => 'nullable',
            // 'add_fee_non_ppn' => 'required',
            'dp_non_ppn' => 'nullable',
        ];

        $data = $request->validate($rules);

        $check = KeranjangJualKonsumen::find($data['keranjang_jual_konsumen_id']);

        if ($check->user_id != Auth::user()->id) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke keranjang ini');
        }
         $data['pembayaran'] = $check->pembayaran;
        // Ambil data keranjang berdasarkan user
        // $keranjang = KeranjangJual::where('keranjang_jual_konsumen_id', $data['keranjang_jual_konsumen_id'])->get();

        // // Validasi berdasarkan jenis barang (PPN atau Non-PPN)
        // $isPpn = $keranjang->where('barang_ppn', 1)->isNotEmpty();
        // $isNonPpn = $keranjang->where('barang_ppn', 0)->isNotEmpty();

        // if ($isPpn) {
        //     $rules = array_merge($rules, [
                // 'diskon' => 'required',
                // 'add_fee' => 'required',
                // 'dp' => 'nullable',
                // 'dp_ppn' => 'nullable',
                // 'dipungut' => 'nullable',
        //     ]);
        // }

        // if ($isNonPpn) {
        //     $rules = array_merge($rules, [
                // 'diskon_non_ppn' => 'required',
                // 'add_fee_non_ppn' => 'required',
                // 'dp_non_ppn' => 'nullable',
        //     ]);
        // }



        // Atur batas waktu eksekusi dan memori
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        // Validasi tambahan untuk konsumen cash
        // if ($data['konsumen_id'] == '*' && $data['pembayaran'] != 1) {
        //     return redirect()->back()->with('error', 'Konsumen cash tidak bisa memilih sistem pembayaran lain selain cash');
        // }

        // Proses checkout
        $db = new KeranjangJual;
        $res = $db->checkoutSales($data);

        if ($res['status'] == 'error') {
            return redirect()->back()->with('error', $res['message']);
        }

        return redirect()->route('sales.jual')->with($res['status'], $res['message']);
    }

    public function order(Request $request)
    {
        if (Auth::user()->karyawan_id == null) {
            return redirect()->back()->with('error', 'Akun belum memiliki Karyawan ID, Silahkan menghubungi Admin.');
        }

        $req = $request->validate([
            'kas_ppn' => 'required|boolean',
        ]);

        $data = InvoiceJualSales::where('karyawan_id', Auth::user()->karyawan_id)->where('kas_ppn', $req['kas_ppn'])->where('is_finished', 0)->get();
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
            // 'diskon' => 'required',
            // 'add_fee' => 'required',
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
                ->where('karyawan_id', Auth::user()->karyawan_id)->where('is_finished', 0)->get();

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

        $karyawan = Auth::user()->karyawan_id;

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

        if ($req['karyawan_id'] != Auth::user()->karyawan_id) {
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

    public function omset_harian_detail_invoice(InvoiceJual $invoice)
    {
        $data = $invoice->load(['konsumen', 'invoice_detail.stok.type', 'invoice_detail.stok.barang', 'invoice_detail.stok.unit', 'invoice_detail.stok.kategori', 'invoice_detail.stok.barang_nama']);
        $jam = CarbonImmutable::parse($data->created_at)->translatedFormat('H:i');
        $tanggal = CarbonImmutable::parse($data->created_at)->translatedFormat('d F Y');

        return view('sales.omset-harian.detail-invoice', [
            'data' => $data,
            'jam' => $jam,
            'tanggal' => $tanggal,
        ]);
    }


    public function check_konsumen()
    {
        $konsumen = Konsumen::with(['kode_toko'])->where('active', 1)->where('karyawan_id', Auth::user()->karyawan_id)->get();

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

     public function konsumen_data(Request $request)
    {
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $search = $request->input('search.value');
        $filters = $request->only(['area', 'kecamatan', 'kode_toko', 'status', 'provinsi', 'kabupaten_kota', 'provinsi']);

        $query = Konsumen::with(['provinsi', 'kabupaten_kota', 'kecamatan', 'kode_toko', 'karyawan'])
            ->where('karyawan_id', Auth::user()->karyawan_id)
            ->filter($filters);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%$search%")
                ->orWhere('alamat', 'like', "%$search%");
            });
        }

        $total = $query->count();
        $data = $query->skip($start)->take($length)->get();

        // Format data sesuai kebutuhan DataTables
        $result = [];
        foreach ($data as $d) {
            $result[] = [
                $d->full_kode,
                $d->kode_toko ? $d->kode_toko->kode : '',
                $d->nama,
                $d->provinsi ? $d->provinsi->nama_wilayah : '',
                $d->kabupaten_kota ? $d->kabupaten_kota->nama_wilayah : '',
                $d->kecamatan ? $d->kecamatan->nama_wilayah : '',
                $d->alamat,
            ];
        }

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $result,
        ]);
    }

    public function konsumen(Request $request)
    {
        $wilayah = Wilayah::whereIn('id_level_wilayah', [1, 2, 3])->get()->groupBy('id_level_wilayah');
        $provinsi = $wilayah->get(1, collect());

        if ($request->has('provinsi') && $request->input('provinsi') != '') {
            $prov = Wilayah::find($request->input('provinsi'));
            $kabupaten_kota = Wilayah::where('id_induk_wilayah', $prov->id_wilayah)->where('id_level_wilayah', 2)->get();
        } else {
            $kabupaten_kota = $wilayah->get(2, collect());
        }

        if ($request->has('kabupaten_kota') && $request->input('kabupaten_kota') != '') {
            $kab = Wilayah::find($request->input('kabupaten_kota'));
            $kecamatan_filter = Wilayah::where('id_induk_wilayah', $kab->id_wilayah )->where('id_level_wilayah', 3)->get();
        } else {
            $kecamatan_filter = $wilayah->get(3, collect());
        }

        $sales_area = Karyawan::with('jabatan')->whereHas('jabatan', function ($query) {
            $query->where('is_sales', 1);
        })->select('id', 'nama')->get();

        return view('sales.konsumen', [
            'provinsi' => $provinsi,
            'kabupaten_kota' => $kabupaten_kota,
            // 'sales_area' => $sales_area,
            'kode_toko' => KodeToko::select('id', 'kode')->get(),
            'kecamatan_filter' => $kecamatan_filter,
        ]);
    }

    public function invoice_konsumen(Request $request)
    {
        $filters = $request->only(['expired', 'apa_ppn', 'konsumen_id', 'kecamatan_id', 'kabupaten_id']);

        $karyawanUserId = Auth::user()->karyawan_id;

        // $filters['karyawan_id'] = $karyawanUserId;

        $konsumen = Konsumen::with('kode_toko')->where('karyawan_id', $karyawanUserId)->get();

        // get konsumen id from $konsumen collection
        $konsumenIds = $konsumen->pluck('id')->all();

        $data = InvoiceJual::gabung($filters)->whereIn('konsumen_id', $konsumenIds);
        $ppn = Pajak::where('untuk', 'ppn')->first()->persen;
        $sales = Karyawan::with('jabatan')->whereHas('jabatan', function ($query) {
                    $query->where('is_sales', 1);
                })->select('id', 'nama')->get();

        // Get unique kecamatan_id and kabupaten_kota_id directly as arrays to minimize memory usage
        $kecamatanIds = Konsumen::where('karyawan_id', $karyawanUserId)->distinct()->pluck('kecamatan_id')->filter()->all();
        $kabupatenIds = Konsumen::where('karyawan_id', $karyawanUserId)->distinct()->pluck('kabupaten_kota_id')->filter()->all();

        // Fetch only needed Wilayah records
        $kabupaten = Wilayah::whereIn('id', $kabupatenIds)->get();
        $kecamatan = Wilayah::whereIn('id', $kecamatanIds)
                    ->when(
                        ($request->has('kabupaten_id') && $request->kabupaten_id != ''),
                        function ($query) use ($request) {
                            $wilayah = Wilayah::find($request->kabupaten_id)->id_wilayah;
                            return $query->where('id_induk_wilayah', $wilayah);
                        }
                    )->get();

        return view('sales.invoice.index',
        [
            'data' => $data,
            'ppn' => $ppn,
            'sales' => $sales,
            'kecamatan' => $kecamatan,
            'kabupaten' => $kabupaten,
            'konsumen' => $konsumen,
        ]);
    }

    public function invoice_konsumen_detail(InvoiceJual $invoice)
    {
        if ($invoice->karyawan_id != Auth::user()->karyawan_id) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk melihat data ini');
        }

         $jam = CarbonImmutable::parse($invoice->created_at)->translatedFormat('H:i');
        $tanggal = CarbonImmutable::parse($invoice->created_at)->translatedFormat('d F Y');

        $tanggal_tempo = $invoice->sistem_pembayaran !== 1 ? Carbon::parse($invoice->jatuh_tempo)->translatedFormat('d F Y') : '-';

        $terbilang = $invoice->sistem_pembayaran !== 1 ? ucwords($this->pembilang($invoice->sisa_tagihan)) : ucwords($this->pembilang($invoice->grand_total));
         $ppn = Pajak::where('untuk', 'ppn')->first()->persen;
        return view('sales.invoice.detail', [
             'data' => $invoice->loadMissing([
                'konsumen',
                'karyawan',
                'invoice_detail.stok.type',
                'invoice_detail.stok.barang',
                'invoice_detail.stok.barang.satuan',
                'invoice_detail.stok.unit',
                'invoice_detail.stok.kategori',
                'invoice_detail.stok.barang_nama',
                'invoice_jual_cicil',
            ]),
            'jam' => $jam,
            'ppn' => $ppn,
            'tanggal_tempo' => $tanggal_tempo,
            'tanggal' => $tanggal,
            'terbilang' => $terbilang,
        ]);
    }

    public function omset_tahunan_konsumen(Request $request)
    {
        // Cek apakah request dari DataTables (AJAX)
        if ($request->ajax()) {
            $tahun = $request->input('tahun', date('Y'));
            $unitId = $request->input('barang_unit_id');

            $kodeTokoId = $request->input('kode_toko_id'); // <--- Tangkap input baru
            $statusOmset = $request->input('status_omset');
            $salesId = Auth::user()->karyawan_id;
            $kabupatenKotaId = $request->input('kabupaten_kota_id');
            $kecamatanId = $request->input('kecamatan_id');
            $statusInvoice = $request->input('status_invoice');

            $bulan = $request->input('bulan', []); // Tangkap filter bulan (array)

            $db = new InvoiceJual;

            $query = $db->getOmsetQuery($tahun, $unitId, $kodeTokoId, $statusOmset, $salesId, $kabupatenKotaId, $kecamatanId, $statusInvoice, $bulan);

            $sql = $query->toSql();
            $bindings = $query->getBindings();

            $grandTotals = DB::table(DB::raw("($sql) as temp_total"))
                ->setBindings($bindings)
                ->selectRaw('
                    COALESCE(SUM(bulan_1), 0) as sum_b1,
                    COALESCE(SUM(bulan_2), 0) as sum_b2,
                    COALESCE(SUM(bulan_3), 0) as sum_b3,
                    COALESCE(SUM(bulan_4), 0) as sum_b4,
                    COALESCE(SUM(bulan_5), 0) as sum_b5,
                    COALESCE(SUM(bulan_6), 0) as sum_b6,
                    COALESCE(SUM(bulan_7), 0) as sum_b7,
                    COALESCE(SUM(bulan_8), 0) as sum_b8,
                    COALESCE(SUM(bulan_9), 0) as sum_b9,
                    COALESCE(SUM(bulan_10), 0) as sum_b10,
                    COALESCE(SUM(bulan_11), 0) as sum_b11,
                    COALESCE(SUM(bulan_12), 0) as sum_b12,
                    COALESCE(SUM(total_setahun), 0) as sum_total
                ')->first();
            // Opsi: Jika difilter unit, hanya tampilkan konsumen yg pernah beli unit itu
            // if ($unitId) {
            //     $query->whereNotNull('transaksi.konsumen_id');
            // }

            // ------------------------------------------------------------------
            // LANGKAH 4: Return ke DataTables
            // ------------------------------------------------------------------
            return DataTables::of($query)
                ->with('grand_totals', $grandTotals)
                ->addIndexColumn()
                ->addColumn('kode_toko', function($row){
                    return $row->kode_toko->kode ?? $row->kode_toko_id ?? '-';
                })
                // Format angka menjadi Rupiah untuk tampilan
                ->editColumn('kabupaten_kota.nama_wilayah', function($row) {
                    $nama = $row->kabupaten_kota?->nama_wilayah ?? '-';
                    return str_replace(['Kab. ', 'Kota '], '', $nama);
                })
                ->editColumn('kecamatan.nama_wilayah', function($row) {
                    $nama = $row->kecamatan?->nama_wilayah ?? '-';
                    return str_replace(['Kec. '], '', $nama);
                })
                ->editColumn('bulan_1', fn($row) => number_format($row->bulan_1 ?? 0, 0, ',', '.'))
                ->editColumn('bulan_2', fn($row) => number_format($row->bulan_2 ?? 0, 0, ',', '.'))
                ->editColumn('bulan_3', fn($row) => number_format($row->bulan_3 ?? 0, 0, ',', '.'))
                ->editColumn('bulan_4', fn($row) => number_format($row->bulan_4 ?? 0, 0, ',', '.'))
                ->editColumn('bulan_5', fn($row) => number_format($row->bulan_5 ?? 0, 0, ',', '.'))
                ->editColumn('bulan_6', fn($row) => number_format($row->bulan_6 ?? 0, 0, ',', '.'))
                ->editColumn('bulan_7', fn($row) => number_format($row->bulan_7 ?? 0, 0, ',', '.'))
                ->editColumn('bulan_8', fn($row) => number_format($row->bulan_8 ?? 0, 0, ',', '.'))
                ->editColumn('bulan_9', fn($row) => number_format($row->bulan_9 ?? 0, 0, ',', '.'))
                ->editColumn('bulan_10', fn($row) => number_format($row->bulan_10 ?? 0, 0, ',', '.'))
                ->editColumn('bulan_11', fn($row) => number_format($row->bulan_11 ?? 0, 0, ',', '.'))
                ->editColumn('bulan_12', fn($row) => number_format($row->bulan_12 ?? 0, 0, ',', '.'))
                ->addColumn('total', function($row) {
                    return '<strong>'.number_format($row->total_setahun ?? 0, 0, ',', '.').'</strong>';
                })
                ->rawColumns(['total'])
                ->filterColumn('nama', function($q, $keyword) {
                    $q->where('konsumens.nama', 'like', "%{$keyword}%");
                })
                ->make(true);
        }

        // Jika bukan AJAX, tampilkan View awal
        $units = BarangUnit::select('id', 'nama')->orderBy('nama')->get();
        $kodeTokos = KodeToko::select('id', 'kode')->get();
        $sales = Karyawan::with('jabatan')->whereHas('jabatan', function ($query) {
                    $query->where('is_sales', 1);
                })->select('id', 'nama')->where('id', Auth::user()->karyawan_id)->get();
        $kabupatenKota = Wilayah::where('id_level_wilayah', 2)->get();
        // $kecamatan = Wilayah::where('id_level_wilayah', 3)->get();
        return view('sales.omset-bulanan-konsumen.index', compact('units', 'kodeTokos', 'sales', 'kabupatenKota'));
    }

    public function print_omset(Request $request)
    {
        // Kita naikkan memory limit sedikit saja untuk query data
        ini_set('memory_limit', '256M');
        set_time_limit(300);

        $tahun = $request->input('tahun', date('Y'));
        $unitId = $request->input('barang_unit_id');
        $kodeTokoId = $request->input('kode_toko_id'); // <--- Tangkap input baru
        $statusOmset = $request->input('status_omset');
        $salesId = Auth::user()->karyawan_id; // Hanya tampilkan data untuk sales yang sedang login
        $kabupatenKotaId = $request->input('kabupaten_kota_id');
        $kecamatanId = $request->input('kecamatan_id');
        $statusInvoice = $request->input('status_invoice');

        $bulan = $request->input('bulan', []);
        $db = new InvoiceJual;
        // Ambil Data
        $laporan = $db->getOmsetQuery($tahun, $unitId, $kodeTokoId, $statusOmset, $salesId, $kabupatenKotaId, $kecamatanId, $statusInvoice, $bulan)
            ->orderBy('konsumens.kode', 'asc') // Sort default untuk cetak
            ->get();

        // Nama Unit untuk Judul
        $namaUnit = 'Semua Perusahaan';
        if ($unitId) {
            $unitDB = DB::table('barang_units')->where('id', $unitId)->first();
            $namaUnit = $unitDB->nama ?? '-';
        }

        return view('sales.omset-bulanan-konsumen.print', compact('laporan', 'tahun', 'namaUnit', 'bulan'));
    }

    public function export_excel_omset(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        $unitId = $request->input('barang_unit_id');
        $kodeTokoId = $request->input('kode_toko_id');
        $statusOmset = $request->input('status_omset');
        $salesId = Auth::user()->karyawan_id; // Hanya export untuk sales yang sedang login
        $kabupatenKotaId = $request->input('kabupaten_kota_id');
        $kecamatanId = $request->input('kecamatan_id');
        $statusInvoice = $request->input('status_invoice');

        // [BARU] Tangkap array bulan
        $bulan = $request->input('bulan', []);

        $fileName = 'Omset_Konsumen_' . $tahun . '.csv';

        $headers = [
            'Content-type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename=' . $fileName,
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        // [MODIFIKASI] Gunakan 'use ($bulan)' pada callback
        $callback = function() use ($tahun, $unitId, $kodeTokoId, $statusOmset, $salesId, $kabupatenKotaId, $kecamatanId, $statusInvoice, $bulan) {
            $file = fopen('php://output', 'w');

            // 1. Susun Header Excel Secara Dinamis
            $csvHeaders = [
                'No', 'Kode Konsumen', 'Kode Toko', 'Nama Toko','Kab/Kota','Kecamatan', 'Sales Area'
            ];

            $namaBulan = [
                1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
                7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
            ];

            // Looping header bulan (tambahkan hanya jika bulan tidak difilter atau termasuk yang dipilih)
            for ($i = 1; $i <= 12; $i++) {
                if (empty($bulan) || in_array((string)$i, $bulan)) {
                    $csvHeaders[] = $namaBulan[$i];
                }
            }
            $csvHeaders[] = 'Total'; // Tambahkan Total di akhir

            fputcsv($file, $csvHeaders);

            $db = new InvoiceJual;

            // 2. Ambil Query
            $query = $db->getOmsetQuery($tahun, $unitId, $kodeTokoId, $statusOmset, $salesId, $kabupatenKotaId, $kecamatanId, $statusInvoice, $bulan)
                ->orderBy('konsumens.kode', 'asc');

            // 3. CHUNKING
            $no = 1;
            $query->chunk(500, function($konsumens) use ($file, &$no, $bulan) { // [MODIFIKASI] use $bulan
                foreach ($konsumens as $row) {
                    // Siapkan baris data awal
                    $csvRow = [
                        $no++,
                        $row->full_kode,
                        $row->kode_toko->kode ?? $row->kode_toko_id ?? '-',
                        $row->nama,
                        str_replace(['Kab. ', 'Kota '], '', $row->kabupaten_kota?->nama_wilayah) ?? '-',
                        str_replace(['Kec. '], '', $row->kecamatan?->nama_wilayah) ?? '-',
                        $row->karyawan?->nama ?? '-'
                    ];

                    // Looping nilai per bulan secara dinamis
                    for ($i = 1; $i <= 12; $i++) {
                        if (empty($bulan) || in_array((string)$i, $bulan)) {
                            $kolomBulan = 'bulan_' . $i;
                            $csvRow[] = $row->$kolomBulan ?? 0;
                        }
                    }

                    $csvRow[] = $row->total_setahun ?? 0; // Masukkan total di akhir

                    fputcsv($file, $csvRow);
                }
                flush();
            });

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    public function detail_omset_page($konsumenId, $bulan, $tahun, Request $request)
    {
        $unitId = $request->input('unit_id');
        $statusInvoice = $request->input('status_invoice');
        $bulanFilter = $request->input('bulanFilter'); // Format: "1,2,3" atau "total"

        // 1. Ambil Data Konsumen
        $konsumen = Konsumen::with('kode_toko')->where('id', $konsumenId)->select('nama', 'kode', 'kode_toko_id')->first();
        if (!$konsumen) abort(404, 'Konsumen tidak ditemukan');

        // 2. Query Detail Transaksi
        $query = DB::table('invoice_jual_details as d')
            ->join('invoice_juals as i', 'd.invoice_jual_id', '=', 'i.id')
            ->join('barangs as b', 'd.barang_id', '=', 'b.id')
            ->leftJoin('barang_namas as bn', 'b.barang_nama_id', '=', 'bn.id') // Join nama barang untuk info
            ->leftJoin('barang_units as u', 'b.barang_unit_id', '=', 'u.id') // Join unit untuk info
            ->select([
                'i.kode as no_invoice',
                'i.created_at as tanggal',
                'bn.nama as nama_barang',
                'b.kode as kode_barang',
                'u.nama as nama_unit',
                'd.jumlah',
                'd.harga_satuan',
                'd.diskon',
                'd.ppn',
                'd.total'
            ])
            ->where('i.konsumen_id', $konsumenId)
            ->where('i.void', 0) // Pastikan tidak mengambil nota void
            ->whereYear('i.created_at', $tahun);

        // Filter Bulan (Jika bukan 'total', filter per bulan)
       if ($bulan !== 'total') {
            $query->whereMonth('i.created_at', $bulan);

            // Fix: Gunakan createFromDate, paksa (int), dan set tanggal ke 1 agar aman
            $namaBulan = \Carbon\Carbon::createFromDate($tahun, (int)$bulan, 1)->isoFormat('MMMM');
        } else {
           // Jika klik pada kolom "Total" di ujung kanan
            if (!empty($bulanFilter)) {
                $arrayBulan = explode(',', $bulanFilter); // Ubah string "1,2" jadi array [1, 2]

                // Gunakan DB::raw untuk filter array bulan
                $query->whereIn(DB::raw('MONTH(i.created_at)'), $arrayBulan);

                // Menyusun nama bulan dinamis untuk judul (misal: "Januari, Maret, Desember")
                $namaBulanArray = [];
                foreach($arrayBulan as $b) {
                    $namaBulanArray[] = \Carbon\Carbon::createFromDate($tahun, (int)$b, 1)->isoFormat('MMMM');
                }
                $namaBulan = implode(', ', $namaBulanArray);
            } else {
                $namaBulan = "Setahun (Jan - Des)";
            }
        }

        // Filter Unit (Jika ada)
        if ($unitId) {
            $query->where('b.barang_unit_id', $unitId);
        }

        if ($statusInvoice) {
            if ($statusInvoice == 'invoice') {
                $query->where('i.lunas', 0);
            } elseif ($statusInvoice == 'lunas') {
                $query->where('i.lunas', 1);
            }
        }
        // Eksekusi Query
        $details = $query->orderBy('i.created_at', 'asc')->get();

        return view('sales.omset-bulanan-konsumen.detail', compact(
            'details', 'konsumen', 'bulan', 'namaBulan', 'tahun', 'unitId'
        ));
    }


}
