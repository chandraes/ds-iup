<?php

namespace App\Http\Controllers;

use App\Models\db\Barang\BarangStokHarga;
use App\Models\db\Karyawan;
use App\Models\db\Konsumen;
use App\Models\db\Pajak;
use App\Models\db\Supplier;
use App\Models\KasKonsumen;
use App\Models\transaksi\InvoiceBelanja;
use App\Models\transaksi\InvoiceJual;
use App\Models\Wilayah;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function invoice_supplier(Request $request)
    {
        $data = InvoiceBelanja::with(['supplier', 'invoice_belanja_cicil'])->where('kas_ppn', 1)->where('tempo', 1)->where('void', 0);
        // get unique supplier_id from $data
        if ($request->has('supplier_id')) {
            $data->where('supplier_id', $request->supplier_id);
        }

        $data = $data->get();

        $supplierIds = $data->pluck('supplier_id')->unique();

        $supplier = Supplier::where('status', 1)->whereIn('id', $supplierIds)->get();
        $ppn = Pajak::where('untuk', 'ppn')->first()->persen;

        return view('billing.invoice-supplier.index', [
            'data' => $data,
            'supplier' => $supplier,
            'ppn' => $ppn,
        ]);
    }

    public function invoice_supplier_non_ppn(Request $request)
    {
        $data = InvoiceBelanja::with(['supplier', 'invoice_belanja_cicil'])->where('kas_ppn', 0)->where('tempo', 1)->where('void', 0);

        if ($request->has('supplier_id')) {
            $data->where('supplier_id', $request->supplier_id);
        }

        $data = $data->get();
        // get unique supplier_id from $data
        $supplierIds = $data->pluck('supplier_id')->unique();

        $supplier = Supplier::where('status', 1)->whereIn('id', $supplierIds)->get();
        $ppn = Pajak::where('untuk', 'ppn')->first()->persen;

        return view('billing.invoice-supplier.index-non-ppn', [
            'data' => $data,
            'supplier' => $supplier,
            'ppn' => $ppn,
        ]);
    }

    public function invoice_supplier_cicil(InvoiceBelanja $invoice, Request $request)
    {
        $data = $request->validate([
            'nominal' => 'required',
            'ppn' => 'required',
            'apa_ppn' => 'required|boolean',
        ]);

        $data['invoice_belanja_id'] = $invoice->id;

        $db = new InvoiceBelanja;

        $res = $db->cicil($data);

        return redirect()->back()->with($res['status'], $res['message']);

    }

    public function invoice_supplier_void(InvoiceBelanja $invoice)
    {
        $db = new InvoiceBelanja;

        return redirect()->back()->with('error', 'Fitur sedang dalam perbaikan, Silahkan hubungi admin untuk manual void sementara waktu.');

        $res = $db->void($invoice->id);

        return redirect()->back()->with($res['status'], $res['message']);
    }

    public function invoice_supplier_bayar(InvoiceBelanja $invoice)
    {
        $db = new InvoiceBelanja;
        // dd($invoice);
        $res = $db->bayar($invoice->id);

        return redirect()->back()->with($res['status'], $res['message']);
    }

    public function invoice_supplier_detail(InvoiceBelanja $invoice)
    {
        return view('billing.invoice-supplier.detail', [
            'data' => $invoice->load(['items.barang.type.unit', 'items.barang.kategori']),
        ]);
    }

    public function invoice_konsumen(Request $request)
    {
        $filters = $request->only(['expired']);

        $data = InvoiceJual::billing($filters, 1, 0);

        $ppn = Pajak::where('untuk', 'ppn')->first()->persen;

        return view('billing.invoice-konsumen.index', [
            'data' => $data,
            'ppn' => $ppn,
        ]);
    }

    public function invoice_konsumen_titipan(Request $request)
    {
        $filters = $request->only(['expired']);
        $titipan = 1;
        $data = InvoiceJual::billing($filters, 1, $titipan);

        $ppn = Pajak::where('untuk', 'ppn')->first()->persen;

        return view('billing.invoice-konsumen.index', [
            'data' => $data,
            'titipan' => $titipan,
            'ppn' => $ppn,
        ]);
    }

    public function invoice_konsumen_non_ppn(Request $request)
    {
        $filters = $request->only(['expired']);
        $data = InvoiceJual::billing($filters, 0, 0);

        return view('billing.invoice-konsumen.index-non-ppn', [
            'data' => $data,
        ]);
    }

    public function invoice_konsumen_titipan_non_ppn(Request $request)
    {
        $titipan = 1;
        $filters = $request->only(['expired']);
        $data = InvoiceJual::billing($filters, 0, $titipan);

        return view('billing.invoice-konsumen.index-non-ppn', [
            'data' => $data,
            'titipan' => $titipan,
        ]);
    }

    public function invoice_konsumen_download(Request $request)
    {
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        $filters = $request->only(['expired', 'kas_ppn', 'titipan']);
        $data = InvoiceJual::billing($filters, $filters['kas_ppn'] ?? 0, $filters['titipan'] ?? 0);
        $stringTitipan = $filters['titipan'] ?? 1 ? 'Titipan' : 'Tempo';
        $stringKas = $filters['kas_ppn'] ?? 1 ? 'PPN' : 'NON PPN';

        $pdf = Pdf::loadview('billing.invoice-konsumen.pdf', [
            'data' => $data,
            'stringKas' => $stringKas,
            'stringTitipan' => $stringTitipan,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Invoice Konsumen '.$stringKas.' '.$stringTitipan.'.pdf');
    }

    public function invoice_konsumen_detail(InvoiceJual $invoice)
    {
        $data = $invoice->load(['konsumen', 'invoice_detail.stok.type', 'invoice_detail.stok.barang', 'invoice_detail.stok.unit', 'invoice_detail.stok.kategori', 'invoice_detail.stok.barang_nama', 'invoice_jual_cicil', 'invoice_detail.barang.satuan', 'invoice_detail.satuan_grosir']);
        $jam = CarbonImmutable::parse($data->created_at)->translatedFormat('H:i');
        $tanggal = CarbonImmutable::parse($data->created_at)->translatedFormat('d F Y');

        return view('billing.invoice-konsumen.detail', [
            'data' => $data,
            'jam' => $jam,
            'tanggal' => $tanggal,
        ]);
    }

    public function invoice_konsumen_bayar(InvoiceJual $invoice)
    {
        $db = new InvoiceJual;

        $res = $db->bayar($invoice->id);

        return redirect()->back()->with($res['status'], $res['message']);
    }

    public function invoice_konsumen_void(InvoiceJual $invoice)
    {
        $db = new InvoiceJual;

        $res = $db->void($invoice->id);

        return redirect()->back()->with($res['status'], $res['message']);
    }

    public function invoice_konsumen_cicil(InvoiceJual $invoice, Request $request)
    {
        $data = $request->validate([
            'nominal' => 'required',
            'ppn' => 'nullable',
            'apa_ppn' => 'required|boolean',
        ]);

        $db = new InvoiceJual;

        $data['invoice_jual_id'] = $invoice->id;
        $data['apa_ppn'] = 0;
        $res = $db->cicil($data);

        return redirect()->back()->with($res['status'], $res['message']);
    }

    public function invoice_konsumen_all(Request $request)
    {
        $filters = $request->validate([
            'expired' => ['nullable', 'in:yes,no'], 'apa_ppn' => ['nullable', 'in:yes,no'],
            'karyawan_id' => ['nullable', 'integer', 'min:1'], 'konsumen_id' => ['nullable', 'integer', 'min:1'],
            'kecamatan_id' => ['nullable', 'string', 'max:255'], 'kabupaten_id' => ['nullable', 'string', 'max:255'],
        ]);
        $ppn = Pajak::where('untuk', 'ppn')->firstOrFail()->persen;
        $sales = Karyawan::whereHas('jabatan', fn ($query) => $query->where('is_sales', 1))->get(['id', 'nama']);
        $kabupaten = Wilayah::whereIn('id', Konsumen::select('kabupaten_kota_id')->whereNotNull('kabupaten_kota_id'))
            ->get(['id', 'nama_wilayah']);
        $kecamatan = Wilayah::whereIn('id', Konsumen::select('kecamatan_id')->whereNotNull('kecamatan_id'))
            ->when(! empty($filters['kabupaten_id']), function ($query) use ($filters): void {
                $query->whereIn('id_induk_wilayah', Wilayah::select('id_wilayah')->where('id', $filters['kabupaten_id']));
            })->get(['id', 'nama_wilayah']);
        $invoiceTableConfig = [
            'data' => route('billing.invoice-konsumen.all.data'),
            'paymentData' => route('billing.invoice-konsumen.all.payment-data', ':id'),
            'history' => route('billing.invoice-konsumen.all.history', ':id'),
            'detail' => route('billing.invoice-konsumen.detail', ':id'),
            'print' => route('billing.form-jual.invoice', ['invoice' => ':id', 'reprint' => 1]),
            'pay' => route('billing.invoice-konsumen.bayar', ':id'),
            'void' => route('billing.invoice-konsumen.void', ':id'),
            'installment' => route('billing.invoice-konsumen.cicil', ':id'),
            'passwordCheck' => route('pengaturan.password-konfirmasi-cek'),
            'privileged' => in_array($request->user()->role, ['su', 'admin'], true),
            'filters' => $filters,
        ];

        return response()->view('billing.invoice-konsumen.all', compact('ppn', 'sales', 'kecamatan', 'kabupaten', 'invoiceTableConfig'))
            ->header('Cache-Control', 'no-store, private');
    }

    public function invoice_konsumen_all_download(Request $request)
    {
        // dd('Fitur sedang dalam perbaikan, Silahkan hubungi admin untuk manual download sementara waktu.', $request->all());
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        $filters = $request->only(['expired', 'apa_ppn', 'karyawan_id', 'konsumen_id', 'kecamatan_id', 'kabupaten_id']);
        $data = InvoiceJual::gabung($filters);

        $pdf = Pdf::loadview('billing.invoice-konsumen.pdf-all', [
            'data' => $data,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Invoice-Konsumen-Tempo.pdf');
    }

    public function invoice_konsumen_edit(InvoiceJual $invoice)
    {
        // Mengambil data beserta relasinya
        $data = $invoice->load([
            'konsumen',
            'invoice_detail.stok.type',
            'invoice_detail.barang.satuan',
            'invoice_detail.barang',
            'invoice_detail.stok.barang',
            'invoice_detail.stok.unit',
            'invoice_detail.stok.kategori',
            'invoice_detail.stok.barang_nama',
            'invoice_jual_cicil',

        ]);

        $jam = CarbonImmutable::parse($data->created_at)->translatedFormat('H:i');
        $tanggal = CarbonImmutable::parse($data->created_at)->translatedFormat('d F Y');

        return view('billing.invoice-konsumen.edit', [
            'data' => $data,
            'jam' => $jam,
            'tanggal' => $tanggal,
        ]);
    }

    public function invoice_konsumen_update(Request $request, InvoiceJual $invoice)
    {
        $request->validate([
            'detail_id' => 'required|array',
            'qty' => 'required|array',
        ]);

        try {
            DB::beginTransaction();

            // 1. TANGKAP TOTAL TAGIHAN LAMA SEBELUM ADA PERUBAHAN
            $totalTagihanLama = $invoice->grand_total; // Atau $invoice->total (pastikan sesuai dengan field acuan Anda)

            $submittedDetailIds = $request->detail_id;
            $submittedQty = $request->qty;
            $detailsLama = $invoice->invoice_detail;

            $totalHargaBaru = 0;
            $totalPpnBaru = 0;

            foreach ($detailsLama as $detail) {

                // Cari data record stok TERBARU di gudang untuk barang_id ini
                $stokTerbaru = BarangStokHarga::where('barang_id', $detail->barang_id)
                    ->where('hide', 0)
                    ->latest('id')
                    ->lockForUpdate()
                    ->first();

                // -------------------------------------------------------------
                // KONDISI A: BARANG DIHAPUS TOTAL OLEH USER
                // -------------------------------------------------------------
                if (! in_array($detail->id, $submittedDetailIds)) {
                    if ($stokTerbaru) {
                        // Semua barang dikembalikan ke record stok TERBARU
                        $stokTerbaru->stok += $detail->jumlah;
                        $stokTerbaru->save();
                    }

                    $detail->delete();

                    continue;
                }

                // -------------------------------------------------------------
                // KONDISI B: BARANG TETAP ADA, CEK PENGURANGAN QUANTITY
                // -------------------------------------------------------------
                $qtyBaru = (int) $submittedQty[$detail->id];
                $qtyLama = (int) $detail->jumlah;
                $selisihQty = $qtyBaru - $qtyLama;

                // Proteksi Back-end
                if ($selisihQty > 0) {
                    DB::rollBack();

                    return redirect()->back()->with('error', 'Gagal update! Anda tidak diperbolehkan menambah jumlah barang pada mode edit ini.');
                }

                if ($selisihQty < 0) {
                    // Kembalikan nilai absolut selisih ke stok TERBARU
                    if ($stokTerbaru) {
                        $stokTerbaru->stok += abs($selisihQty);
                        $stokTerbaru->save();
                    }
                }

                // -------------------------------------------------------------
                // RE-KALKULASI NOMINAL DETAIL BARANG YANG TERSISA
                // -------------------------------------------------------------
                $hargaSatuanFinal = $detail->harga_satuan - $detail->diskon + $detail->ppn;

                $detail->jumlah = $qtyBaru;
                $detail->total = $qtyBaru * $hargaSatuanFinal;
                $detail->save();

                $totalHargaBaru += $detail->total;
                $totalPpnBaru += ($detail->ppn * $qtyBaru);
            }

            // -------------------------------------------------------------
            // RE-KALKULASI MASTER DATA INVOICE
            // -------------------------------------------------------------
            $invoice->total = $totalHargaBaru;
            $invoice->grand_total = $totalHargaBaru;

            $invoice->ppn = $totalPpnBaru;

            if (isset($invoice->dp_ppn) && $invoice->dp_ppn > 0) {
                $invoice->sisa_ppn = $totalPpnBaru - $invoice->dp_ppn;
            } else {
                $invoice->sisa_ppn = $totalPpnBaru;
            }

            if ($invoice->sisa_ppn < 0) {
                $invoice->sisa_ppn = 0;
            }

            $totalDP = isset($invoice->dp) ? $invoice->dp : 0;

            $totalCicilan = 0;
            if ($invoice->invoice_jual_cicil) {
                $sumNominalCicil = $invoice->invoice_jual_cicil->sum('nominal');
                $sumPpnCicil = $invoice->invoice_jual_cicil->sum('ppn');
                $totalCicilan = $sumNominalCicil + $sumPpnCicil;
            }

            $invoice->sisa_tagihan = $totalHargaBaru - $totalDP - $totalCicilan;

            if ($invoice->sisa_tagihan <= 0) {
                $invoice->sisa_tagihan = 0;
                $invoice->lunas = 1;
            } else {
                $invoice->lunas = 0;
            }

            $invoice->save();

            // -------------------------------------------------------------
            // AREA CUSTOM KAS KONSUMEN / PLAFON HUTANG
            // -------------------------------------------------------------
            // 2. HITUNG SELISIH (PENGURANGAN) TAGIHAN
            $selisihTagihan = $totalTagihanLama - $totalHargaBaru;

            // Jika ada selisih (berarti ada barang yang dikurangi/dihapus)
            if ($selisihTagihan > 0) {

                $dbKas = new KasKonsumen;
                $konsumenId = $invoice->konsumen_id;

                $sisaTerakhir = $dbKas->sisaTerakhir($konsumenId);
                $penguranganSisa = $sisaTerakhir - $selisihTagihan;

                if ($penguranganSisa < 0) {
                    $penguranganSisa = 0; // Pastikan tidak menjadi negatif
                }

                $dbKas->create([
                    'konsumen_id' => $konsumenId,
                    'invoice_jual_id' => $invoice->id,
                    'uraian' => 'Update Invoice - '.$invoice->full_kode,
                    'bayar' => $selisihTagihan,
                    'sisa' => $penguranganSisa,
                ]);

            }
            // -------------------------------------------------------------

            // $ppnKeluaran = PpnKeluaran::where('invoice_jual_id', $invoice->id)->first();

            // if ($ppnKeluaran) {
            //     $ppnKeluaran->nominal = $totalPpnBaru;
            //     $ppnKeluaran->save();
            // }

            DB::commit();

            return redirect()->route('billing.invoice-konsumen.detail', $invoice->id)
                ->with('success', 'Isi Invoice Berhasil Dikurangi & Stok Gudang Terbaru Telah Diperbarui!');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Terjadi kesalahan sistem saat memproses data: '.$e->getMessage());
        }
    }
}
