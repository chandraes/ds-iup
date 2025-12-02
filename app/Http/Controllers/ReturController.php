<?php

namespace App\Http\Controllers;

use App\Models\Config;
use App\Models\db\Barang\BarangKategori;
use App\Models\db\Barang\BarangUnit;
use App\Models\ReturSupplier;
use App\Models\ReturSupplierDetail;
use App\Models\StokRetur;
use App\Models\StokReturCart;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReturController extends Controller
{
    public function stok_retur_data(Request $request)
    {
        if ($request->ajax()) {
            $userId = Auth::id();

            // Query Utama dengan Left Join ke Keranjang User
            // Tujuannya agar kita tahu row mana yang sedang ada di keranjang user tersebut
            $query = StokRetur::with(['barang.unit', 'barang.kategori', 'barang.satuan', 'barang.barang_nama'])
                    ->leftJoin('stok_retur_carts', function($join) use ($userId) {
                        $join->on('stok_returs.id', '=', 'stok_retur_carts.stok_retur_id')
                            ->where('stok_retur_carts.user_id', '=', $userId);
                    })
                    ->where('stok_returs.total_qty_karantina', '>', 0)
                    ->select(
                        'stok_returs.*',
                        'stok_retur_carts.qty as cart_qty', // Ambil qty di keranjang
                        'stok_retur_carts.id as cart_id'    // Ambil ID keranjang
                    );

            // --- Logic Filter (Tetap sama) ---
            if ($request->has('unit_filter') && $request->unit_filter != '') {
                $query->whereHas('barang', function($q) use ($request) {
                    $q->where('barang_unit_id', $request->unit_filter);
                });
            }
            if ($request->has('kategori_filter') && $request->kategori_filter != '') {
                $query->whereHas('barang', function($q) use ($request) {
                    $q->where('barang_kategori_id', $request->kategori_filter);
                });
            }

            return datatables()->of($query)
                ->addIndexColumn()
                // ... (Kolom perusahaan s/d non_ppn tetap sama) ...
                ->addColumn('perusahaan', function($row){ return $row->barang->unit->nama ?? '-'; })
                ->addColumn('kelompok', function($row){ return $row->barang->kategori->nama ?? '-'; })
                ->addColumn('nama_barang', function($row){ return $row->barang->barang_nama->nama ?? '-'; })
                ->addColumn('kode_barang', function($row){ return $row->barang->kode ?? '-'; })
                ->addColumn('merk', function($row){ return $row->barang->merk ?? '-'; })
                ->addColumn('stok_retur', function($row){
                    return '<span class="fw-bold text-danger">'.number_format($row->total_qty_karantina).'</span>';
                })
                ->addColumn('satuan', function($row){ return $row->barang->satuan->nama ?? '-'; })
                ->addColumn('ppn', function($row){ return ($row->barang->jenis == 1) ? '<span class="badge bg-success">Ya</span>' : '-'; })
                ->addColumn('non_ppn', function($row){ return ($row->barang->jenis == 2) ? '<span class="badge bg-success">Ya</span>' : '-'; })
                ->addColumn('detail_sumber', function($row){
                    return '<button type="button" class="btn btn-sm btn-info text-white btn-history"
                            data-id="'.$row->id.'" data-nama="'.$row->barang->barang_nama->nama.'">
                            <i class="bi bi-clock-history"></i> Lihat</button>';
                })
                // --- MODIFIKASI KOLOM AKSI ---
                ->addColumn('aksi', function($row){
                    // Siapkan data JSON untuk modal
                    $dataJson = json_encode([
                        'id' => $row->id, // Stok Retur ID
                        'barang_nama' => $row->barang->barang_nama->nama,
                        'stok_max' => $row->total_qty_karantina,
                        'satuan' => $row->barang->satuan->nama ?? '',
                        'current_qty' => $row->cart_qty ?? 0 // Qty di keranjang saat ini
                    ]);

                    // Jika sudah ada di keranjang, tombol berwarna Kuning (Edit)
                    if($row->cart_qty > 0) {
                        return '<button type="button" class="btn btn-warning btn-sm btn-cart-action text-dark"
                                data-row=\''.$dataJson.'\'>
                                <i class="bi bi-pencil-square"></i> Edit ('.$row->cart_qty.')
                            </button>';
                    }

                    // Jika belum ada, tombol Biru (Tambah)
                    return '<button type="button" class="btn btn-primary btn-sm btn-cart-action"
                            data-row=\''.$dataJson.'\'>
                            <i class="bi bi-cart-plus"></i> Tambah
                        </button>';
                })
                ->rawColumns(['stok_retur', 'ppn', 'non_ppn', 'detail_sumber', 'aksi'])
                ->make(true);
        }
    }

    public function stok_retur(Request $request)
    {
       $units = BarangUnit::all();
        $kategoris = BarangKategori::all();
        // 5. Kirim data dan nilai filter ke view
        return view('billing.barang-retur-kirim.index', [
            'units' => $units,
            'kategoris' => $kategoris
        ]);
    }

    public function stok_retur_sumber($id)
    {
        $stokRetur = StokRetur::with([
            'barang.barang_nama',
            'barang.satuan',
            // Load sampai ke konsumen dan barang stok harga (batch asal)
            'sources.detail.barang_retur.konsumen.kode_toko',
            'sources.detail.stok'
        ])->findOrFail($id);

        // Kita return berupa Partial View (HTML potongan)
        return view('billing.barang-retur-kirim.partials.history', compact('stokRetur'));
    }

    public function emptyCart()
    {
        StokReturCart::where('user_id', Auth::id())->delete();
        return response()->json(['status' => 'success', 'message' => 'Keranjang berhasil dikosongkan.']);
    }

    // Method untuk mendapatkan info badge keranjang (Optional, untuk update UI real-time)
    public function getCartInfo()
    {
        $count = StokReturCart::where('user_id', Auth::id())->sum('qty'); // atau count() rows
        return response()->json(['total_items' => $count]);
    }

    public function addToCart(Request $request)
    {
        $request->validate([
            'stok_retur_id' => 'required|exists:stok_returs,id',
            'qty'           => 'required|integer|min:1',
        ]);

        $user_id = Auth::id();

        // Ambil data Bad Stok sumber
        $stokRetur = StokRetur::with('barang.unit')->findOrFail($request->stok_retur_id);

        // Validasi 1: Cek Stok Tersedia
        if ($request->qty > $stokRetur->total_qty_karantina) {
            return response()->json(['status' => 'error', 'message' => 'Qty melebihi stok retur yang tersedia!']);
        }

        // Cek apakah user sudah punya keranjang?
        $existingCart = StokReturCart::where('user_id', $user_id)->first();

        // Validasi 2: Cek Konsistensi Supplier (Unit)
        if ($existingCart) {
            // Jika unit barang yang mau ditambah BEDA dengan yang ada di keranjang
            if ($existingCart->barang_unit_id != $stokRetur->barang->barang_unit_id) {
                // Ambil nama supplier lama untuk pesan error
                $oldUnitName = BarangUnit::find($existingCart->barang_unit_id)->nama ?? 'Lainnya';
                return response()->json([
                    'status' => 'error',
                    'message' => "Keranjang Anda berisi barang dari supplier: <b>$oldUnitName</b>.<br>Harap selesaikan transaksi tersebut atau kosongkan keranjang sebelum mengganti supplier."
                ]);
            }
        }

        // Simpan / Update Keranjang
        // Cek apakah item ini sudah ada di keranjang (duplicate item)
        $cartItem = StokReturCart::where('user_id', $user_id)
                    ->where('stok_retur_id', $request->stok_retur_id)
                    ->first();

        if ($cartItem) {
            // Cek total qty jika digabung
            if ($request->qty > $stokRetur->total_qty_karantina) {
                return response()->json(['status' => 'error', 'message' => 'Total Qty di keranjang melebihi stok tersedia!']);
            }
            $cartItem->update(['qty' => $request->qty]);
        } else {
            StokReturCart::create([
                'user_id'        => $user_id,
                'stok_retur_id'  => $request->stok_retur_id,
                'barang_unit_id' => $stokRetur->barang->barang_unit_id,
                'qty'            => $request->qty
            ]);
        }

        return response()->json(['status' => 'success', 'message' => 'Berhasil masuk keranjang!']);
    }

    // ==========================================
    // 2. HALAMAN REVIEW KERANJANG
    // ==========================================
    public function cartIndex()
    {
        $carts = StokReturCart::with(['stok_retur.barang.barang_nama', 'stok_retur.barang.satuan', 'stok_retur.barang.unit'])
                ->where('user_id', Auth::id())
                ->get();

        // Ambil info supplier dari item pertama (karena validasi menjamin semua sama)
        $supplier = $carts->first() ? $carts->first()->stok_retur->barang->unit : null;

        return view('billing.barang-retur-kirim.cart', compact('carts', 'supplier'));
    }

    // ==========================================
    // 3. UPDATE & DELETE KERANJANG
    // ==========================================
    public function updateCart(Request $request)
    {
        $cart = StokReturCart::with('stok_retur')->findOrFail($request->id);

        if($request->qty > $cart->stok_retur->total_qty_karantina){
             return response()->json(['status' => 'error', 'message' => 'Qty melebihi stok!']);
        }

        $cart->update(['qty' => $request->qty]);
        return response()->json(['status' => 'success']);
    }

    public function deleteCart($id)
    {
        StokReturCart::where('id', $id)->where('user_id', Auth::id())->delete();
        return back()->with('success', 'Item dihapus dari keranjang.');
    }

    // ... namespace dan use tetap sama ...

    public function processCheckout(Request $request)
    {
        $user_id = Auth::id();
        $carts = StokReturCart::with('stok_retur.barang')->where('user_id', $user_id)->get();

        if($carts->isEmpty()) {
            return back()->with('error', 'Keranjang kosong.');
        }

        DB::beginTransaction();
        try {
            // [UBAH DISINI] Logika Nomor Integer
            // Ambil nomor terakhir, jika tidak ada mulai dari 0, lalu tambah 1
            $lastNomor = ReturSupplier::max('nomor');
            $nomorBaru = $lastNomor ? ($lastNomor + 1) : 1;

            // 1. Buat Header Invoice
            $returSupplier = ReturSupplier::create([
                'nomor'          => $nomorBaru,        // <-- Kolom 'nomor' (Integer)
                'tanggal'        => Carbon::now(),
                'barang_unit_id' => $carts->first()->barang_unit_id,
                'user_id'        => $user_id,
            ]);

            foreach ($carts as $cart) {
                // 2. Buat Detail
                ReturSupplierDetail::create([
                    'retur_supplier_id' => $returSupplier->id,
                    'barang_id'         => $cart->stok_retur->barang_id,
                    'qty'               => $cart->qty
                ]);

                // 3. Potong Stok Retur
                $stokRetur = StokRetur::where('id', $cart->stok_retur_id)->lockForUpdate()->first();

                if($stokRetur->total_qty_karantina < $cart->qty) {
                    throw new \Exception("Stok barang {$stokRetur->barang->barang_nama->nama} berubah dan tidak mencukupi.");
                }

                $stokRetur->decrement('total_qty_karantina', $cart->qty);
                $stokRetur->increment('total_qty_diproses', $cart->qty);
            }

            // 4. Hapus Keranjang
            StokReturCart::where('user_id', $user_id)->delete();

            DB::commit();

            return redirect()->route('billing.stok-retur')
                             ->with('success', 'Transaksi Berhasil. Nomor: ' . $nomorBaru);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses: ' . $e->getMessage());
        }
    }

    public function invoiceIndex()
    {
        $units = BarangUnit::all(); // Untuk filter
        return view('billing.barang-retur-kirim.invoice-index', compact('units'));
    }

   public function invoiceShow($id)
    {
        // [UBAH DISINI] Load relasi 'barang_unit'
        $invoice = ReturSupplier::with(['barang_unit', 'user', 'details.barang.barang_nama', 'details.barang.satuan'])->findOrFail($id);
        return view('billing.barang-retur-kirim.partials.invoice-detail', compact('invoice'));
    }

    public function invoiceData(Request $request)
    {
        if ($request->ajax()) {
            // [UBAH DISINI] Ganti 'unit' menjadi 'barang_unit'
            $query = ReturSupplier::with(['barang_unit', 'user'])
                    ->withCount('details');

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('created_at', [
                    $request->start_date . ' 00:00:00',
                    $request->end_date . ' 23:59:59'
                ]);
            }

            if ($request->filled('unit_filter')) {
                $query->where('barang_unit_id', $request->unit_filter);
            }

            return datatables()->of($query)
                ->addIndexColumn()
                ->addColumn('nomor_display', function($row){
                    return '<span class="fw-bold font-monospace text-primary">RS-' . sprintf('%04d', $row->nomor) . '</span>';
                })
                ->editColumn('created_at', function($row){
                    return $row->created_at->format('Y-m-d');
                })
                // [UBAH DISINI] Akses relasi 'barang_unit'
                ->addColumn('supplier', function($row){
                    return $row->barang_unit->nama ?? '-';
                })
               // --- LOGIKA KOLOM 1: TAHAP PROSES (Packing) ---
                ->addColumn('status_proses', function($row){
                    // Jika 0: Masih disini (Kuning)
                    // Jika > 0: Sudah lewat (Centang Hijau)
                    if ($row->tipe == 0) {
                        return '<span class="badge bg-warning text-dark"><i class="bi bi-box-seam"></i> Diproses</span>';
                    } elseif ($row->tipe > 0 && $row->tipe != 99) {
                        return '<span class="text-success"><i class="bi bi-check-circle-fill fs-5"></i></span>';
                    } else {
                        return '<span class="text-muted">-</span>'; // Void
                    }
                })

                // --- LOGIKA KOLOM 2: TAHAP PENGIRIMAN ---
                ->addColumn('status_kirim', function($row){
                    // Jika 0: Belum sampai sini (Abu-abu)
                    // Jika 1: Sedang disini (Biru)
                    // Jika 2: Selesai (Centang Hijau)
                    if ($row->tipe == 0) {
                        return '<span class="text-muted opacity-25"><i class="bi bi-dash-lg"></i></span>';
                    } elseif ($row->tipe == 1) {
                        return '<span class="badge bg-info text-dark"><i class="bi bi-truck"></i> Jalan</span>';
                    } elseif ($row->tipe == 2) {
                        return '<span class="badge bg-success"><i class="bi bi-check-all"></i> Diterima</span>';
                    } else {
                        return '<span class="badge bg-danger">Void</span>';
                    }
                })
                ->addColumn('total_item', function($row){
                    return '<span class="badge bg-light text-dark border">' . $row->details_count . ' Item</span>';
                })
                ->addColumn('aksi', function($row){
                    $btn = '<div class="btn-group" role="group">';

                    // Tombol Detail
                    $btn .= '<button class="btn btn-sm btn-outline-secondary btn-detail" data-id="'.$row->id.'" title="Lihat Detail"><i class="bi bi-eye"></i></button>';

                    // Tombol Kirim/Cetak (Hanya jika status aktif)
                    if ($row->tipe != 99) {
                        $label = ($row->tipe == 0) ? 'Kirim' : 'Cetak';
                        $icon  = ($row->tipe == 0) ? 'bi-send-fill' : 'bi-printer';
                        $class = ($row->tipe == 0) ? 'btn-primary' : 'btn-secondary';

                        // Link ke Route Print
                        $url = route('billing.penyelesaian-retur.print', $row->id);

                        $btn .= '<a href="'.$url.'" target="_blank" class="btn btn-sm '.$class.' reload-on-click" title="'.$label.' PDF">
                                    <i class="bi '.$icon.'"></i> '.$label.'
                                 </a>';
                    }

                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['nomor_display', 'status_proses', 'status_kirim', 'total_item', 'aksi'])
                ->make(true);
        }

    }

    public function printPdf($id)
    {
        $invoice = ReturSupplier::with(['barang_unit', 'details.barang.barang_nama', 'details.barang.satuan', 'user'])
                    ->findOrFail($id);

        // LOGIC: Jika status masih 0 (Diproses), ubah jadi 1 (Dikirim)
        if ($invoice->tipe == 0) {
            $invoice->update(['tipe' => 1]);
        }

        // Generate PDF
        // 'nomor_invoice' di bawah hanyalah string format tampilan
        $invoice->nomor_invoice = 'RS-' . sprintf('%04d', $invoice->nomor);

        $pt = Config::where('untuk', 'resmi' )->first();

        $tanggal = Carbon::parse($invoice->updated_at)->format('d-m-Y');
        $pdf = Pdf::loadView('billing.barang-retur-kirim.pdf.surat-jalan', compact('invoice', 'pt', 'tanggal'));

        // Stream (Buka di tab baru) dengan nama file custom
        return $pdf->stream('Surat_Jalan_Retur_'.$invoice->nomor_invoice.'.pdf');
    }
}
