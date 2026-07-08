<?php

namespace App\Http\Controllers;

use App\Models\Config;
use App\Models\db\Barang\Barang;
use App\Models\db\Barang\BarangKategori;
use App\Models\db\Barang\BarangStokHarga;
use App\Models\db\Barang\BarangUnit;
use App\Models\GroupWa;
use App\Models\ReturSupplier;
use App\Models\ReturSupplierDetail;
use App\Models\ReturSupplierReceipt;
use App\Models\ReturSupplierReceiptDetail;
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
        // Load invoice beserta details dan receipts
        $invoice = ReturSupplier::with([
            'barang_unit',
            'user',
            'details.barang.barang_nama',
            'details.barang.satuan',
            'receipts.details' // Tarik detail riwayat penerimaan
        ])->findOrFail($id);

        // Kumpulkan semua item yang pernah diproses di riwayat penerimaan (termasuk barang pengganti baru)
        $processedItems = collect();

        // 1. Masukkan barang asli dari invoice detail terlebih dahulu
        foreach ($invoice->details as $detail) {
            $processedItems->put($detail->barang_id, [
                'barang' => $detail->barang,
                'qty_awal' => $detail->qty,
                'diterima' => 0,
                'batal' => 0,
                'catatan' => []
            ]);
        }

        // 2. Kalkulasi akumulasi dari riwayat penerimaan (Receipts)
        foreach ($invoice->receipts as $receipt) {
            foreach ($receipt->details as $rd) {
                // Jika ada barang pengganti baru (tidak ada di invoice awal), buat baris baru
                if (!$processedItems->has($rd->barang_id)) {
                    $processedItems->put($rd->barang_id, [
                        'barang' => $rd->barang,
                        'qty_awal' => 0, // Barang pengganti tidak ada qty awal di invoice retur
                        'diterima' => 0,
                        'batal' => 0,
                        'catatan' => []
                    ]);
                }

                // Ambil data itemnya
                $item = $processedItems->get($rd->barang_id);

                // Akumulasikan nilainya
                if ($rd->status_proses == 'terima') {
                    $item['diterima'] += $rd->qty_terima;
                } elseif ($rd->status_proses == 'hapus') {
                    $item['batal'] += $rd->qty_terima;
                }

                // Simpan catatan jika ada
                if ($rd->catatan_item) {
                    $item['catatan'][] = $rd->catatan_item;
                }

                $processedItems->put($rd->barang_id, $item);
            }
        }

        return view('billing.barang-retur-kirim.partials.invoice-detail', compact('invoice', 'processedItems'));
    }

    public function invoiceData(Request $request)
    {
        if ($request->ajax()) {
            // Load relasi terbaru
            $query = ReturSupplier::with(['barang_unit', 'user', 'details', 'receipts.details'])
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
                ->addColumn('supplier', function($row){
                    return $row->barang_unit->nama ?? '-';
                })
                // --- STATUS KIRIM ---
                ->addColumn('status_kirim', function($row){
                    if ($row->tipe == 0) {
                        return '<span class="text-muted opacity-25"><i class="bi bi-dash-lg"></i></span>';
                    } elseif ($row->tipe == 1) {
                        return '<span class="badge bg-primary"><i class="bi bi-truck"></i> Dikirim</span>';
                    } elseif ($row->tipe == 2) {
                        return '<span class="badge bg-info text-dark border border-info"><i class="bi bi-arrow-repeat"></i> Parsial</span>';
                    } elseif ($row->tipe == 3) {
                        return '<span class="badge bg-success"><i class="bi bi-check-all"></i> Selesai</span>';
                    } else {
                        return '<span class="badge bg-danger">Void</span>';
                    }
                })
                // --- PROGRESS INFO ---
                ->addColumn('progress_info', function($row){
                    $totalAwal = $row->details->sum('qty');
                    $totalDiterima = 0;
                    $totalBatal = 0;

                    foreach ($row->receipts as $receipt) {
                        foreach ($receipt->details as $rd) {
                            if ($rd->status_proses == 'terima') $totalDiterima += $rd->qty_terima;
                            if ($rd->status_proses == 'hapus') $totalBatal += $rd->qty_terima;
                        }
                    }

                    $sisa = $totalAwal - ($totalDiterima + $totalBatal);

                    $html = '<div class="d-flex flex-column" style="font-size: 0.85em;">';
                    $html .= '<span class="fw-bold text-dark mb-1">Total: '.$totalAwal.' Item</span>';

                    if ($row->tipe > 0 && $row->tipe != 99) {
                        $html .= '<span class="text-success"><i class="bi bi-check-circle"></i> Masuk Stok: <b>'.$totalDiterima.'</b></span>';
                        $html .= '<span class="text-danger"><i class="bi bi-x-circle"></i> Batal Retur: <b>'.$totalBatal.'</b></span>';
                        if ($sisa > 0) {
                            $html .= '<span class="text-warning text-dark"><i class="bi bi-hourglass-split"></i> Menunggu: <b>'.$sisa.'</b></span>';
                        }
                    } else {
                        $html .= '<span class="text-muted fst-italic">Belum ada proses</span>';
                    }
                    $html .= '</div>';

                    return $html;
                })
                // --- KOLOM AKSI KEMBALI DIMASUKKAN ---
                ->addColumn('aksi', function($row){
                    $btn = '<div class="btn-group" role="group">';

                    // Tombol Detail
                    $btn .= '<button class="btn btn-sm btn-outline-secondary btn-detail" data-id="'.$row->id.'" title="Lihat Detail"><i class="bi bi-eye"></i></button>';

                    if ($row->tipe != 99) {
                        $urlPrint = route('billing.penyelesaian-retur.print', $row->id);

                        if ($row->tipe == 0) {
                            $btn .= '<a href="'.$urlPrint.'" class="btn btn-sm btn-primary btn-kirim-confirm" title="Kirim & Cetak"><i class="bi bi-send-fill"></i> Kirim</a>';
                        } else {
                            // Munculkan tombol terima hanya jika status Dikirim (1) atau Parsial (2)
                            if (in_array($row->tipe, [1, 2])) {
                                $urlVerify = route('billing.penyelesaian-retur.verify', $row->id);
                                $btn .= '<a href="'.$urlVerify.'" class="btn btn-sm btn-success" title="Terima Barang Retur"><i class="bi bi-box-arrow-in-down"></i> Terima</a>';
                            }
                            // Tombol Cetak
                            $btn .= '<a href="'.$urlPrint.'" target="_blank" class="btn btn-sm btn-secondary" title="Cetak Ulang"><i class="bi bi-printer"></i> Cetak</a>';
                        }
                    }

                    $btn .= '</div>';
                    return $btn;
                })
                // Pastikan 'aksi' terdaftar di rawColumns
                ->rawColumns(['nomor_display', 'status_kirim', 'progress_info', 'aksi'])
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

        $dbWa = new GroupWa();
        $pesan = '';
        $tanggal = Carbon::now()->translatedFormat('d F Y');

        // $pesan = "*".$data->barang_unit->nama."*\n";
        $pesan .= "◆◆◆◆◆◆◆◆◆◆◆◆"."\n"."*KIRIM BARANG RETUR*\n"."◆◆◆◆◆◆◆◆◆◆◆◆\n\n";

        $pesan .= "*".$invoice->barang_unit?->nama."*\n\n";

        $pesan .= "*Tanggal* : ".$tanggal."\n\n";

        //  $pesan = "Barang A: \n";

        $n = 1;
        foreach ($invoice->load(['details.barang.satuan'])->details as $d) {
            $pesan .= $n++.'. '.$d->barang->barang_nama->nama." ".$d->barang->kode.""."\n".$d->barang->merk." "."....... ". $d->nf_qty.' ('.$d->barang->satuan->nama.")";
            $pesan .= "\n\n";
        }

        $tujuan = $dbWa->where('untuk', 'kirim-retur-supplier')->first()->nama_group;

        $dbWa->sendWa($tujuan, $pesan);

        // Generate PDF
        // 'nomor_invoice' di bawah hanyalah string format tampilan
        $invoice->nomor_invoice = 'RS-' . sprintf('%04d', $invoice->nomor);

        $pt = Config::where('untuk', 'resmi' )->first();

        $tanggal = Carbon::parse($invoice->updated_at)->format('d-m-Y');
        $pdf = Pdf::loadView('billing.barang-retur-kirim.pdf.surat-jalan', compact('invoice', 'pt', 'tanggal'));

        // Stream (Buka di tab baru) dengan nama file custom
        return $pdf->stream('Surat_Jalan_Retur_'.$invoice->nomor_invoice.'.pdf');
    }

    public function verifyShow($id)
    {
        $invoice = ReturSupplier::with([
            'barang_unit',
            'user',
            'details.barang.barang_nama',
            'details.barang.satuan'
        ])->findOrFail($id);

        if (!in_array($invoice->tipe, [1, 2])) {
            return redirect()->route('billing.penyelesaian-retur.index')
                            ->with('error', 'Status transaksi tidak valid untuk penerimaan.');
        }

        $barangPengganti = Barang::with(['barang_nama', 'satuan'])
                            ->where('barang_unit_id', $invoice->barang_unit_id)
                            ->get();

        // --- LOGIKA BARU: Menghitung Sisa Qty ---
        $detailsDenganSisa = collect();

        foreach ($invoice->details as $detail) {
            // Hitung total qty yang SUDAH DIPROSES untuk barang ini di invoice ini
            // (Mencakup status 'terima' maupun 'hapus')
            $totalDiproses = ReturSupplierReceiptDetail::whereHas('receipt', function($query) use ($id) {
                $query->where('retur_supplier_id', $id);
            })
            ->where('barang_id', $detail->barang_id)
            ->sum('qty_terima');

            // Hitung sisa
            $sisa = $detail->qty - $totalDiproses;

            // Simpan sisa_qty ke dalam object detail agar bisa dibaca di Blade & JS
            $detail->sisa_qty = $sisa;
            $detailsDenganSisa->push($detail);
        }

        // Timpa relasi details dengan data yang sudah memiliki sisa_qty
        $invoice->setRelation('details', $detailsDenganSisa);
        // ----------------------------------------

        return view('billing.barang-retur-kirim.verify', compact('invoice', 'barangPengganti'));
    }

   public function verifySubmit(Request $request, $id)
    {
        // 1. Validasi Input Minimal Harus Ada Barang
        $request->validate([
            'barang_id' => 'required|array',
            'barang_id.*' => 'required|exists:barangs,id',
            'qty_terima' => 'required|array',
            'qty_terima.*' => 'required|integer|min:1',
        ]);

        // Sertakan relasi 'details' (item bawaan awal invoice) agar bisa dihitung sisanya nanti
        $invoice = ReturSupplier::with('details')->findOrFail($id);

        // Proteksi status keamanan ganda
        if (!in_array($invoice->tipe, [1, 2])) {
            return redirect()->route('billing.penyelesaian-retur.index')
                            ->with('error', 'Transaksi gagal! Status invoice sudah berubah.');
        }

        try {
            DB::beginTransaction();

            // 2. Buat Header Log Penerimaan Retur
            $receipt = new ReturSupplierReceipt();
            $receipt->retur_supplier_id = $invoice->id;
            $receipt->user_id = Auth::id() ?? 1; // Mengambil ID user login (fallback ke 1 jika test tanpa login)
            $receipt->catatan = $request->catatan;
            $receipt->save();

            // 3. Looping Data Barang yang Diinput User untuk Ditambah ke Stok
            foreach ($request->barang_id as $key => $barangId) {
                $qtyTerima = $request->qty_terima[$key];
                $statusProses = $request->status_proses[$key]; // 'terima' atau 'hapus'
                $catatanItem = $request->catatan_item[$key] ?? null;

                // Simpan detail riwayat tanda terima (history)
                $receiptDetail = new ReturSupplierReceiptDetail();
                $receiptDetail->receipt_id = $receipt->id; // Sesuai struktur database Anda
                $receiptDetail->barang_id = $barangId;
                $receiptDetail->qty_terima = $qtyTerima;
                $receiptDetail->status_proses = $statusProses;
                $receiptDetail->catatan_item = $catatanItem;
                $receiptDetail->save();

                // 4. LOGIKA UTAMA: Masuk Stok ATAU Buang
                if ($statusProses == 'terima') {
                    // JIKA DITERIMA: Tambah ke stok
                    $stokHargaTerakhir = BarangStokHarga::where('barang_id', $barangId)
                                                        ->orderBy('id', 'desc')
                                                        ->lockForUpdate()
                                                        ->first();

                    if (!$stokHargaTerakhir) {
                        throw new \Exception("Barang (ID: {$barangId}) belum memiliki riwayat harga/stok awal di sistem.");
                    }
                    $stokHargaTerakhir->increment('stok', $qtyTerima);
                }
                else if ($statusProses == 'hapus') {
                    // JIKA DIHAPUS (Batal Retur):
                    // Kita TIDAK menambah stok. History sudah tersimpan di $receiptDetail di atas.
                }
            }

            // =====================================================================
            // 5. LOGIKA BARU: EVALUASI AUTOMATIC COMPLETION (ANTI-BARANG PENGGANTI BEDA QTY)
            // =====================================================================

            // Ambil semua riwayat detail penerimaan untuk invoice ini (termasuk yang baru disimpan di atas)
            $semuaRiwayat = ReturSupplierReceiptDetail::whereHas('receipt', function($query) use ($id) {
                $query->where('retur_supplier_id', $id);
            })->get();

            $semuaBarangAsliSelesai = true;

            // Periksa sisa antrean khusus untuk barang-barang bawaan asli invoice
            foreach ($invoice->details as $detail) {
                // Hitung total akumulasi qty (terima + hapus) khusus untuk barang asli ini
                $diprosesUntukItemIni = $semuaRiwayat->where('barang_id', $detail->barang_id)->sum('qty_terima');

                // Hitung sisa target barang asli
                $sisaItemAsli = $detail->qty - $diprosesUntukItemIni;

                // Jika masih ada barang asli yang memiliki sisa antrean (> 0), gagalkan otomatisasi "Selesai"
                if ($sisaItemAsli > 0) {
                    $semuaBarangAsliSelesai = false;
                    break; // Keluar dari loop item asli karena transaksi fungsionalnya belum selesai penuh
                }
            }

            // Tentukan status tipe akhir invoice:
            // Jika user mencentang manual "Tandai Selesai" ATAU semua item asli sudah tuntas diproses
            if (($request->has('status_selesai') && $request->status_selesai == '1') || $semuaBarangAsliSelesai) {
                $invoice->tipe = 3; // Selesai Penuh
            } else {
                $invoice->tipe = 2; // Diterima Sebagian / Parsial
            }

            $invoice->save();
            // =====================================================================

            DB::commit();

            $pesanSukses = $invoice->tipe == 3
                ? 'Stok berhasil diverifikasi. Seluruh item invoice asli telah terpenuhi (Status: SELESAI).'
                : 'Stok berhasil diverifikasi dan dimasukkan ke dalam sistem (Status: PARSIAL).';

            return redirect()->route('billing.penyelesaian-retur.index')
                            ->with('success', $pesanSukses);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                            ->withInput()
                            ->with('error', 'Terjadi Kesalahan: ' . $e->getMessage());
        }
    }
}
