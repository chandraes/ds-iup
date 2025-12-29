<?php

namespace App\Http\Controllers;

use App\Models\db\Barang\Barang;
use App\Models\db\Barang\BarangKategori;
use App\Models\db\Barang\BarangNama;
use App\Models\db\Barang\BarangUnit;
use App\Models\db\Pajak;
use App\Models\db\Supplier;
use App\Models\transaksi\Keranjang;
use App\Models\transaksi\KeranjangBeli;
use App\Models\transaksi\KeranjangBeliDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class FormBeliController extends Controller
{

    public function index()
    {
        $data = KeranjangBeli::where('user_id', Auth::user()->id)->withCount('details')->withSum('details', 'total')->get();
        $supplier = BarangUnit::select('id', 'nama')->get();
        $ppnRate = Pajak::where('untuk', 'ppn')->first()->persen;

        return view('billing.form-beli.index', compact('data', 'supplier', 'ppnRate'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sistem_pembayaran' => 'required|in:1,2',
            'kas_ppn' => 'required|boolean',
            'barang_unit_id' => 'required|exists:barang_units,id'
        ]);

        $data['user_id'] = Auth::user()->id;

        $store = KeranjangBeli::create($data);

        return redirect()->route('billing.form-beli.detail', $store->id);
    }

    public function delete(KeranjangBeli $keranjang)
    {
        $keranjang->delete();

        return redirect()->back()->with('success', 'Berhasil Menghapus Transaksi');
    }

    public function detail(KeranjangBeli $keranjang)
    {
        $detail = $keranjang->details();
        $selectKategori = BarangKategori::all();
        $selectBarangNama = BarangNama::select('id', 'nama')->distinct()->orderBy('id')->get();

        return view('billing.form-beli.detail', [
            'b' => $keranjang,
            'keranjang' => $detail,
            'selectBarangNama' => $selectBarangNama,
            'selectKategori' => $selectKategori
        ]);
    }

    public function detail_datatable(KeranjangBeli $keranjang, Request $request)
    {
        $keranjangMap = $keranjang->details->mapWithKeys(function ($detail) {
            return [
                $detail->barang_id => [
                    'harga' => $detail->harga,
                    'qty' => $detail->qty,
                    'id' => $detail->id // Ini adalah 'barang_retur_detail_id'
                ]
            ];
        });

        $jenis = $keranjang->kas_ppn == 1 ? 1 : 2;
            // TIPE 2 (Dari Konsumen) -> Tampilkan daftar BARANG (Produk)
        $query = Barang::with(['barang_nama', 'satuan', 'kategori'])
            ->select('barangs.*')
            ->withHbLama()
            ->where('barangs.jenis', $jenis)
            ->where('barangs.barang_unit_id', $keranjang->barang_unit_id)
            ->withSum(['stok_harga' => function($q) {
                $q->where('stok', '>', 0);
            }], 'stok');



        if ($request->filled('kategori')) {
            $query->where('barang_kategori_id', $request->input('kategori'));
        }

        if ($request->filled('barang_nama')) {
            $query->where('barang_nama_id', $request->input('barang_nama'));
        }
        // === AKHIR BAGIAN BARU ===

        // Teruskan $query yang SUDAH DIFILTER ke DataTables
        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('stok_info', function ($row) {
                return number_format($row->stok_harga_sum_stok, 0 , ',','.');
            })
            ->addColumn('action', function ($row) use ($keranjangMap) {
                $row->nf_stok = number_format($row->stok_harga_sum_stok, 0 , ',','.');

                $rowData = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');
                $barangId = $row->id;
                $hargaLama = $row->hb_lama ?? 0;
                $formatedHargaLama = number_format($hargaLama, 0, ',', '.');
                // 2. Cek apakah barang ini ada di map keranjang kita
                if ($keranjangMap->has($barangId)) {

                    // JIKA ADA (Mode Edit)
                    $detail = $keranjangMap->get($barangId);
                    $harga = $detail['harga'];
                    $qty = $detail['qty'];

                    $detailId = $detail['id'];
                    $qtyFormatted = number_format($qty, 0, ',', '.');
                    $satuan = $row->satuan->nama ?? 'PCS';

                    // Buat tombol "Edit" (hijau) yang menampilkan Qty
                    return '<button type="button" class="btn btn-success btn-sm btn-modal-trigger" '.
                        ' data-row=\'' . $rowData . '\' '.
                        ' data-qty="' . $qty . '" '. // <= Kirim Qty
                        ' data-harga="'. $harga .'" '.
                        ' data-harga-lama="'. $formatedHargaLama .'" '.
                        ' data-detail-id="' . $detailId . '">'. // <= Kirim Detail ID
                        $qtyFormatted . ' ' . $satuan .
                        '</button>';

                } else {

                    // JIKA TIDAK ADA (Mode Tambah Baru)

                    // Buat tombol "Pilih" (biru) seperti biasa
                    return '<button type="button" class="btn btn-primary btn-sm btn-modal-trigger" '.
                        ' data-row=\'' . $rowData . '\' '.
                        ' data-qty="0" '. // <= Qty adalah 0
                        ' data-harga="0" '. // <= Qty adalah 0
                        ' data-harga-lama="'. $formatedHargaLama .'" '.
                        ' data-detail-id="0">'. // <= Detail ID adalah 0
                        'Pilih'.
                        '</button>';
                }
            })
            ->rawColumns(['nama_barang', 'action'])
            ->make(true);
    }

    public function detail_store(KeranjangBeli $keranjang, Request $request)
    {
         $data = $request->validate([
            'barang_id' => 'required|exists:barang_stok_hargas,id',
            'qty' => 'required',
            'harga' => 'required'
        ]);

        $data['qty'] = str_replace('.', '', $data['qty']);
        $data['harga'] = str_replace('.', '', $data['harga']);
        $data['total'] = $data['qty'] * $data['harga'];

        if ($data['qty'] < 0) {
            return redirect()->back()->with('error', 'Jumlah Tidak Boleh dibawah 0!');
        }

        $db = new KeranjangBeliDetail();

        // $stok = BarangStokHarga::find($data['barang_stok_harga_id'])->stok;

        // if ($data['jumlah'] > $stok) {
        //     return redirect()->back()->with('error', 'Jumlah retur melebihi stok yang tersedia (Stok: '.$stok.')');
        // }

        if ($data['qty'] == 0) {
            $res = $db->where('keranjang_beli_id', $keranjang->id)
                ->where('barang_id', $data['barang_id'])
                ->delete();

            $res = ['status' => 'success', 'message' => 'Item berhasil dihapus dari daftar retur'];
        } else {

            $res = $db->updateOrCreate([
                'keranjang_beli_id' => $keranjang->id,
                'barang_id' => $data['barang_id'],
            ],[
                'keranjang_beli_id' => $keranjang->id,
                'barang_id' => $data['barang_id'],
                'qty' => $data['qty'],
                'harga' => $data['harga'],
                'total' => $data['total']
            ]);

            $res = ['status' => 'success', 'message' => 'Item berhasil ditambahkan ke daftar retur'];
        }

        return redirect()->back()->with($res['status'], $res['message']);
    }

    public function detail_empty(KeranjangBeli $keranjang)
    {

        $keranjang->details()->delete();

        return redirect()->back()->with('success', 'Keranjang Berhasil Di kosongkan!');
    }

    public function detail_preview(KeranjangBeli $keranjang)
    {

        $supplier = Supplier::where('barang_unit_id', $keranjang->barang_unit_id)->first();

        if (!$supplier) {
            $message = Auth::user()->role != 'asisten-admin' ? 'Perusahaan ini belum di atur Suppliernya. Silahkan atur terlebih dahulu di Menu Database Supplier!' :
                                            'Perusahaan ini belum di atur Suppliernya. Silahkan hubungi admin untuk mengisi data di Menu Database Supplier!';
            return redirect()->back()->with('error', $message);
        }

        $jatuhTempo = $supplier->pembayaran == 2 ? Carbon::now()->addDays($supplier->tempo_hari)->format('d-m-Y') : '';


        $ppnRate = Pajak::where('untuk', 'ppn')->first()->persen;

        return view('billing.form-beli.keranjang', [
            'b' => $keranjang,
            'keranjang' => $keranjang->details,
            'supplier' => $supplier,
            'ppnRate' => $ppnRate,
            'jatuhTempo' => $jatuhTempo
         ]);
    }

    public function detail_preview_delete(Request $request)
    {
         $data = $request->validate([
            'id' => 'required|exists:keranjang_beli_details,id',
        ]);

        KeranjangBeliDetail::find($data['id'])->delete();

        return response()->json(['status' => 'success', 'message' => 'Item berhasil dihapus dari daftar retur']);
    }

    public function detail_lanjutkan(KeranjangBeli $keranjang, Request $request)
    {

        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'keranjang_beli_id' => 'required|exists:keranjang_belis,id',
            'uraian' => 'required',
            'diskon' => 'required',
            'add_fee' => 'required',
            'uraian' => 'required',
            'dp' => 'nullable',
            'dp_ppn' => 'nullable',
            'jatuh_tempo' => 'nullable',
        ]);

        if (Auth::user()->role == 'asisten-admin') {
            return redirect()->back()->with('error', 'Anda Tidak Memiliki Izin untuk melakukan Eksekusi ini!!');
        }

        $db = new KeranjangBeli;
        $res = $db->checkout($data);

        if ($res['status'] == 'error') {
            return redirect()->back()->with($res['status'], $res['message']);
        }

        return redirect()->route('billing')->with($res['status'], $res['message']);
    }



        // public function index(Request $request)
        // {
        //     $req = $request->validate([
        //         'kas_ppn' => 'required',
        //         'tempo' => 'required',
        //     ]);

        //     $jenis = $req['kas_ppn'] == '1' ? 1 : 2;
        //     $selectKategori = BarangKategori::all();
        //     $selectBarangNama = BarangNama::select('id', 'nama')->distinct()->orderBy('id')->get();

        //     $keranjang = Keranjang::with(['barang.type.unit'])
        //             ->where('user_id', Auth::user()->id)
        //             ->where('jenis', $jenis)
        //             ->where('tempo', $req['tempo'])->get();

        //     return view('billing.form-beli.index', compact('selectBarangNama', 'selectKategori', 'jenis', 'req', 'keranjang'));
        // }
    // public function index(Request $request)
    // {
    //     $req = $request->validate([
    //         'kas_ppn' => 'required',
    //         'tempo' => 'required',
    //     ]);

    //     $supplier = Supplier::where('status', 1)->get();

    //     if ($supplier->count() == 0) {
    //         return redirect()->back()->with('error', 'Belum ada supplier yang aktif, silahkan tambah supplier terlebih dahulu!');
    //     }

    //     $data = BarangUnit::with(['types'])->get();
    //     $ppn = Pajak::where('untuk', 'ppn')->first()->persen;

    //     $jenis = $req['kas_ppn'] == '1' ? 1 : 2;

    //     $keranjang = Keranjang::with(['barang.type.unit'])
    //         ->where('user_id', Auth::user()->id)
    //         ->where('jenis', $jenis)
    //         ->where('tempo', $req['tempo'])->get();

    //     return view('billing.form-beli.index', [
    //         'data' => $data,
    //         'jenis' => $jenis, // 1 = 'tunai', 2 = 'kredit
    //         'req' => $req,
    //         'keranjang' => $keranjang,
    //         'ppnRate' => $ppn,
    //         'supplier' => $supplier,
    //     ]);
    // }

    // public function getSupplier(Request $request)
    // {

    //     $data = Supplier::find($request->id);

    //     return response()->json($data);
    // }

    // public function getKategori(Request $request)
    // {
    //     $barang = Barang::where('barang_type_id', $request->barang_type_id);
    //     // distinct barang_kategori_id from barang to array
    //     $kategori = $barang->distinct()->pluck('barang_kategori_id')->toArray();

    //     $data = BarangKategori::whereIn('id', $kategori)->get();

    //     if ($data->count() == 0) {
    //         return response()->json([
    //             'status' => 0,
    //             'message' => 'Data tidak ditemukan',
    //         ]);
    //     }

    //     return response()->json([
    //         'status' => 1,
    //         'data' => $data,
    //     ]);
    // }

    // public function getBarang(Request $request)
    // {
    //     $jenis = [$request->jenis, 3];
    //     $data = BarangNama::with('barang')->where('barang_kategori_id', $request->barang_kategori_id)
    //         ->whereHas('barang', function ($q) use ($request, $jenis) {
    //             $q->where('barang_type_id', $request->barang_type_id)
    //                 ->whereIn('jenis', $jenis);
    //         })
    //         ->get();

    //     if ($data->count() == 0) {
    //         return response()->json([
    //             'status' => 0,
    //             'message' => 'Data tidak ditemukan',
    //         ]);
    //     }

    //     return response()->json([
    //         'status' => 1,
    //         'data' => $data,
    //     ]);
    // }

    // public function getMerk(Request $request)
    // {
    //     $jenis = [$request->jenis, 3];
    //     $data = Barang::where('barang_nama_id', $request->barang_nama_id)
    //         ->where('barang_kategori_id', $request->barang_kategori_id)
    //         ->where('barang_type_id', $request->barang_type_id)
    //         ->whereIn('jenis', $jenis)
    //         ->get();

    //     if ($data->count() == 0) {
    //         return response()->json([
    //             'status' => 0,
    //             'message' => 'Data tidak ditemukan',
    //         ]);
    //     }

    //     return response()->json([
    //         'status' => 1,
    //         'data' => $data,
    //     ]);
    // }

    // public function getKode(Request $request)
    // {
    //     $data = Barang::leftJoin('satuans as s', 's.id', 'barangs.satuan_id')
    //         ->where('barangs.id', $request->barang_id)
    //         ->select('barangs.*', 's.nama as satuan')
    //         ->first();

    //     if (! $data) {
    //         return response()->json([
    //             'status' => 0,
    //             'message' => 'Data tidak ditemukan',
    //         ]);
    //     }

    //     return response()->json([
    //         'status' => 1,
    //         'data' => $data,
    //     ]);
    // }

    // public function keranjang_store(Request $request)
    // {
    //     $data = $request->validate([
    //         'barang_id' => 'required|exists:barangs,id',
    //         'jumlah' => 'required',
    //         'harga' => 'required',
    //         'jenis' => 'required',
    //         'tempo' => 'required',
    //     ]);

    //     $data['user_id'] = Auth::user()->id;
    //     $data['jumlah'] = str_replace('.', '', $data['jumlah']);
    //     $data['harga'] = str_replace('.', '', $data['harga']);
    //     $data['total'] = $data['jumlah'] * $data['harga'];

    //     try {
    //         DB::beginTransaction();

    //         Keranjang::create($data);

    //         DB::commit();

    //         return back()->with('success', 'Data berhasil disimpan');

    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         return back()->with('error', $e->getMessage());
    //     }

    // }

    // public function keranjang_delete(Keranjang $keranjang)
    // {
    //     try {
    //         DB::beginTransaction();

    //         $keranjang->delete();

    //         DB::commit();

    //         return back()->with('success', 'Data berhasil dihapus');

    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         return back()->with('error', $e->getMessage());
    //     }
    // }

    // public function keranjang_empty(Request $request)
    // {
    //     $req = $request->validate([
    //         'jenis' => 'required',
    //         'tempo' => 'required',
    //     ]);

    //     try {
    //         DB::beginTransaction();

    //         Keranjang::where('user_id', Auth::user()->id)
    //             ->where('jenis', $req['jenis'])
    //             ->where('tempo', $req['tempo'])
    //             ->delete();

    //         DB::commit();

    //         return back()->with('success', 'Keranjang berhasil dikosongkan');

    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         return back()->with('error', $e->getMessage());
    //     }
    // }

    // public function keranjang_checkout(Request $request)
    // {
    //     $data = $request->validate([
    //         'kas_ppn' => 'required',
    //         'tempo' => 'required',
    //         'supplier_id' => 'required',
    //         'uraian' => 'required',
    //         'diskon' => 'required',
    //         'add_fee' => 'required',
    //         'jenis' => 'required',
    //         'dp' => 'required_if:tempo,1',
    //         'dp_ppn' => 'nullable',
    //         'jatuh_tempo' => 'required_if:tempo,1',
    //     ]);

    //     $db = new KeranjangBeli;

    //     $res = $db->checkout($data);

    //     return redirect()->back()->with($res['status'], $res['message']);
    // }
}
