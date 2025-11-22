<?php

namespace App\Http\Controllers;

use App\Models\BarangRetur;
use App\Models\BarangReturDetail;
use App\Models\Config;
use App\Models\db\Barang\Barang;
use App\Models\db\Barang\BarangKategori;
use App\Models\db\Barang\BarangNama;
use App\Models\db\Barang\BarangStokHarga;
use App\Models\db\Barang\BarangType;
use App\Models\db\Barang\BarangUnit;
use App\Models\db\CostOperational;
use App\Models\db\Karyawan;
use App\Models\db\KelompokRute;
use App\Models\db\Konsumen;
use App\Models\db\Kreditor;
use App\Models\db\Pajak;
use App\Models\GantiRugi;
use App\Models\GroupWa;
use App\Models\Investor;
use App\Models\InvestorModal;
use App\Models\KasBesar;
use App\Models\Pengaturan;
use App\Models\Pengelola;
use App\Models\RekapGaji;
use App\Models\RekapGajiDetail;
use App\Models\StokRetur;
use App\Models\transaksi\InventarisInvoice;
use App\Models\transaksi\InvoiceBelanja;
use App\Models\transaksi\InvoiceJual;
use App\Models\transaksi\InvoiceJualSales;
use App\Models\transaksi\InvoiceJualSalesDetail;
use App\Models\transaksi\KeranjangJual;
use App\Models\transaksi\OrderInden;
use App\Models\transaksi\OrderIndenDetail;
use App\Services\StarSender;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class BillingController extends Controller
{
    public function lihat_stok(Request $request)
    {
        return redirect()->back()->with('error', 'Fitur ini sedang dalam pengembangan. Silakan coba lagi nanti.');
        // $kategori = BarangKategori::with(['barang_nama'])->get();
        // $type = BarangType::with(['unit', 'barangs'])->get();
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
        return view('billing.stok.index', [
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

    public function index()
    {

        $is = InvoiceBelanja::where('tempo', 1)->where('void', 0)->where('kas_ppn', 1)->count();
        $isn = InvoiceBelanja::where('tempo', 1)->where('void', 0)->where('kas_ppn', 0)->count();

        $invoiceJualCounts = InvoiceJual::select(
            DB::raw('COUNT(CASE WHEN kas_ppn = 1 THEN 1 END) as ik'),
            DB::raw('COUNT(CASE WHEN kas_ppn = 1 AND titipan = 1 THEN 1 END) as ikt'),
            DB::raw('COUNT(CASE WHEN kas_ppn = 0 THEN 1 END) as ikn'),
            DB::raw('COUNT(CASE WHEN kas_ppn = 0 AND titipan = 1 THEN 1 END) as iktn')
        )->where('lunas', 0)
            ->where('void', 0)
            ->first();

        $salesOrderCounts = InvoiceJualSales::select(
            DB::raw('COUNT(CASE WHEN kas_ppn = 1 THEN 1 END) as sales_order_ppn'),
            DB::raw('COUNT(CASE WHEN kas_ppn = 0 THEN 1 END) as sales_order_non_ppn'),
        )->where('is_finished', 0)
            ->first();

        $gr = GantiRugi::where('lunas', 0)->count();

        $br = BarangRetur::whereIn('status', [1,2])->count();
        $sr = StokRetur::where('status', 0)->count();

        return view('billing.index', [
            'is' => $is,
            'ik' => $invoiceJualCounts->ik,
            'isn' => $isn,
            'ikn' => $invoiceJualCounts->ikn,
            'gr' => $gr,
            'br' => $br,
            'ikt' => $invoiceJualCounts->ikt,
            'iktn' => $invoiceJualCounts->iktn,
            'sr' => $sr,
            'sales_order_ppn' => $salesOrderCounts->sales_order_ppn,
            'sales_order_non_ppn' => $salesOrderCounts->sales_order_non_ppn,
        ]);
    }

    public function ppn_masuk_susulan()
    {
        $data = Investor::all();
        $im = InvestorModal::where('persentase', '>', 0)->get();

        $pp = Investor::where('nama', 'pengelola')->first()->persentase;
        $pi = Investor::where('nama', 'investor')->first()->persentase;

        return view('billing.ppn-susulan.index', [
            'data' => $data,
            'im' => $im,
            'pp' => $pp,
            'pi' => $pi,
        ]);
    }

    public function ppn_masuk_susulan_store(Request $request)
    {
        $data = $request->validate([
            'nominal' => 'required',
        ]);

        $db = new KasBesar;

        $store = $db->ppn_masuk_susulan($data['nominal']);

        return redirect()->back()->with($store['status'], $store['message']);

    }

    public function cost_operational()
    {
        $data = CostOperational::all();

        if ($data->isEmpty()) {
            return redirect()->route('db.cost-operational')->with('error', 'Data cost operational kosong, silahkan tambahkan data cost operational terlebih dahulu');
        }

        return view('billing.form-cost-operational.form-operational.index', [
            'data' => $data,
        ]);
    }

    public function cost_operational_store(Request $request)
    {
        $data = $request->validate([
            'nominal' => 'required',
            'cost_operational_id' => 'required|exists:cost_operationals,id',
            'nama_rek' => 'required',
            'no_rek' => 'required',
            'bank' => 'required',
        ]);

        $data['ppn_kas'] = 1;

        $db = new KasBesar;

        $res = $db->cost_operational($data);

        return redirect()->route('billing.form-cost-operational')->with($res['status'], $res['message']);

    }

    public function gaji()
    {
        $check = RekapGaji::where('bulan', date('m'))->whereYear('tahun', date('Y'))->first();

        if ($check) {
            return redirect()->route('billing')->with('error', 'Form Gaji Bulan Ini Sudah Dibuat');
        }
        $month = Carbon::now()->locale('id')->monthName;
        $data = Karyawan::with(['jabatan'])->where('status', 1)->get();

        if ($data->count() == 0) {
            return redirect()->back()->with('error', 'Data Staff/Direksi Kosong, Silahkan Tambahkan Data Terlebih Dahulu');
        }

        return view('billing.form-cost-operational.form-gaji.index', [
            'data' => $data,
            'month' => $month,
        ]);
    }

    public function gaji_store(Request $request)
    {
        ini_set('max_execution_time', 300); // 300 seconds = 5 minutes
        ini_set('memory_limit', '512M');

        $ds = $request->validate([
            'total' => 'required',
        ]);

        $data = Karyawan::where('status', 1)->get();

        $db = new KasBesar;
        $saldo = $db->saldoTerakhir(1);

        if ($saldo < $ds['total']) {
            return redirect()->back()->with('error', 'Saldo Kas Besar Tidak Cukup');
        }
        try {
            DB::beginTransaction();
            $rekap = RekapGaji::create([
                'uraian' => 'Gaji Bulan '.date('F').' Tahun '.date('Y'),
                'bulan' => date('m'),
                'tahun' => date('Y'),
                'total' => $ds['total'],
            ]);

            $rekapGajiDetails = []; // Initialize an array to hold all records for bulk insertion

            foreach ($data as $d) {
                $bpjs_tk = $d->apa_bpjs_tk == 1 ? $d->gaji_pokok * 0.049 : 0;
                $potongan_bpjs_tk = $d->apa_bpjs_tk == 1 ? $d->gaji_pokok * 0.02 : 0;
                $bpjs_k = $d->apa_bpjs_kes == 1 ? $d->gaji_pokok * 0.04 : 0;
                $potongan_bpjs_kesehatan = $d->apa_bpjs_kes == 1 ? $d->gaji_pokok * 0.01 : 0;

                $pendapatan_kotor = $d->gaji_pokok + $d->tunjangan_jabatan + $d->tunjangan_keluarga + $bpjs_tk + $bpjs_k;
                $pendapatan_bersih = $pendapatan_kotor - $potongan_bpjs_tk - $potongan_bpjs_kesehatan;

                $rekapGajiDetails[] = [
                    'rekap_gaji_id' => $rekap->id,
                    'nik' => $d->kode.sprintf('%03d', $d->nomor),
                    'nama' => $d->nama,
                    'jabatan' => $d->jabatan->nama,
                    'gaji_pokok' => $d->gaji_pokok,
                    'tunjangan_jabatan' => $d->tunjangan_jabatan,
                    'tunjangan_keluarga' => $d->tunjangan_keluarga,
                    'bpjs_tk' => $bpjs_tk,
                    'bpjs_k' => $bpjs_k,
                    'potongan_bpjs_tk' => $potongan_bpjs_tk,
                    'potongan_bpjs_kesehatan' => $potongan_bpjs_kesehatan,
                    'pendapatan_kotor' => $pendapatan_kotor,
                    'pendapatan_bersih' => $pendapatan_bersih,
                    'nama_rek' => $d->nama_rek,
                    'bank' => $d->bank,
                    'no_rek' => $d->no_rek,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
            }

            // Perform a bulk insert after the loop
            RekapGajiDetail::insert($rekapGajiDetails);

            $arrayKasBesar['uraian'] = 'Gaji Bulan '.date('F').' '.date('Y');
            $arrayKasBesar['tanggal'] = date('Y-m-d');
            $arrayKasBesar['nominal'] = $ds['total'];
            $arrayKasBesar['jenis'] = 0;
            $arrayKasBesar['saldo'] = $saldo - $ds['total'];
            $arrayKasBesar['modal_investor_terakhir'] = $db->modalInvestorTerakhir(1);
            $arrayKasBesar['nama_rek'] = 'Msng2 Karyawan';
            $arrayKasBesar['bank'] = 'BCA';
            $arrayKasBesar['no_rek'] = '-';
            $arrayKasBesar['ppn_kas'] = 1;
            $storeKasBesar = $db->create($arrayKasBesar);

            DB::commit();

        } catch (\Throwable $th) {
            // throw $th;
            DB::rollback();

            return redirect()->back()->with('error', 'Gagal Membuat Form Gaji, '.$th->getMessage());
        }

        $group = GroupWa::where('untuk', 'kas-besar-ppn')->first();

        $pesan = "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                    "*FORM GAJI KARYAWAN*\n".
                    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                    'Nilai :  *Rp. '.number_format($ds['total'], 0, ',', '.')."*\n\n".
                    "Ditransfer ke rek:\n\n".
                    "Nama     : Masing2 Karyawan\n\n".
                    "==========================\n".
                    "Sisa Saldo Kas Besar : \n".
                    'Rp. '.number_format($storeKasBesar->saldo, 0, ',', '.')."\n\n".
                    "Total Modal Investor : \n".
                    'Rp. '.number_format($storeKasBesar->modal_investor_terakhir, 0, ',', '.')."\n\n".
                    "Terima kasih 🙏🙏🙏\n";
        $send = new StarSender($group->nama_group, $pesan);
        $res = $send->sendGroup();

        return redirect()->route('billing.form-cost-operational')->with('success', 'Form Gaji Berhasil Dibuat');
    }

    public function form_inventaris()
    {
        $hi = InventarisInvoice::where('pembayaran', 2)->where('lunas', 0)->where('void', 0)->count();

        return view('billing.form-inventaris.index', [
            'hi' => $hi,
        ]);
    }

    public function form_dividen()
    {
        $persen = Investor::all();
        $pengelola = Pengelola::where('persentase', '>', 0)->get();
        $investor = InvestorModal::where('persentase', '>', 0)->get();

        if ($pengelola->count() == 0 || $investor->count() == 0) {
            return redirect()->back()->with('error', 'Data Pengelola/Investor Belum Di isi!!');
        }

        return view('billing.form-dividen.index', [
            'persen' => $persen,
            'pengelola' => $pengelola,
            'investor' => $investor,
        ]);
    }

    public function form_dividen_store(Request $request)
    {
        $data = $request->validate([
            'nominal' => 'required',
            'ppn_kas' => 'required',
        ]);

        $db = new KasBesar;

        $res = $db->dividen($data);

        return redirect()->route('billing')->with($res['status'], $res['message']);
    }

    public function ganti_rugi(Request $request)
    {
        $data = GantiRugi::with(['barang_stok_harga.barang.satuan', 'barang_stok_harga.barang.barang_nama', 'karyawan'])->where('lunas', 0)
            ->orderBy('karyawan_id');

        if ($request->filled('karyawan')) {
            $data->where('karyawan_id', $request->karyawan);
        }

        $data = $data->get();

        $karyawan = Karyawan::whereHas('ganti_rugi', function ($query) {
            $query->where('lunas', 0);
        })->get();

        return view('billing.ganti-rugi.index', [
            'data' => $data,
            'karyawan' => $karyawan,
        ]);
    }

    public function ganti_rugi_void(GantiRugi $rugi)
    {
        $db = new GantiRugi;

        $res = $db->void($rugi->id);

        return redirect()->back()->with($res['status'], $res['message']);
    }

    public function ganti_rugi_bayar(GantiRugi $rugi, Request $request)
    {
        $data = $request->validate([
            'jenis' => 'required',
            'nominal' => 'required_if:jenis,1',
        ]);

        $db = new GantiRugi;

        $res = $db->bayar($rugi->id, $data);

        return redirect()->back()->with($res['status'], $res['message']);
    }

    public function bunga_investor(Request $request)
    {
        $data = $request->validate([
            'kas_ppn' => 'required|boolean',
        ]);

        $kreditor = Kreditor::where('is_active', 1)->get();

        if ($kreditor->isEmpty()) {
            return redirect()->route('db.kreditor')->with('error', 'Data kreditor kosong, silahkan tambahkan data kreditor terlebih dahulu');
        }
        $db = new KasBesar;
        $modal = $db->modalInvestorTerakhir($data['kas_ppn']) < 0 ? $db->modalInvestorTerakhir($data['kas_ppn']) * -1 : 0;

        $pph_val = Pajak::where('untuk', 'pph-investor')->first()->persen / 100;

        return view('billing.form-bunga-investor.index', [
            'kreditor' => $kreditor,
            'modal' => $modal,
            'pph_val' => $pph_val,
            'kas_ppn' => $data['kas_ppn'],
        ]);
    }

    public function bunga_investor_store(Request $request)
    {
        $data = $request->validate([
            'kas_ppn' => 'required|boolean',
            'kreditor_id' => 'required|exists:kreditors,id',
            'nominal_transaksi' => 'required',
            'transfer_ke' => 'required',
            'no_rekening' => 'required',
            'bank' => 'required',
        ]);

        $db = new KasBesar;

        $res = $db->bunga_investor($data);

        return redirect()->route('billing')->with($res['status'], $res['message']);

    }

    public function sales_order(Request $request)
    {
        $req = $request->validate([
            'kas_ppn' => 'required|boolean',
            'karyawan_id' => 'nullable|exists:karyawans,id',
            'kelompok_rute' => 'nullable|exists:kelompok_rutes,id',
        ]);

        $data = InvoiceJualSales::with(['karyawan', 'konsumen.kode_toko', 'konsumen.kecamatan'])->where('is_finished', 0)->where('kas_ppn', $req['kas_ppn']);

        if (isset($req['karyawan_id']) && $req['karyawan_id'] != '') {
            $data->where('karyawan_id', $req['karyawan_id']);
        }

        if (isset($req['kelompok_rute']) && $req['kelompok_rute'] != '') {
            $data->whereHas('konsumen', function ($query) use ($req) {
                $kec = KelompokRute::find($req['kelompok_rute']);
                if ($kec) {
                    $query->whereIn('kecamatan_id', $kec->details()->pluck('wilayah_id'));
                }
            });
        }


        $data = $data->get();

        $ppn = Pajak::where('untuk', 'ppn')->first()->persen;
        $karyawan = Karyawan::where('jabatan_id', 3)->get();
        $kelompokRute = KelompokRute::all();

        return view('billing.sales-order.index', [
            'data' => $data,
            'ppn' => $ppn,
            'karyawan' => $karyawan,
            'kelompokRute' => $kelompokRute,
        ]);

    }

    public function sales_order_detail(InvoiceJualSales $order)
    {
        $ppn = Pajak::where('untuk', 'ppn')->first()->persen;
        $penyesuaian = Pengaturan::where('untuk', 'penyesuaian_jual')->first()->nilai;

        return view('billing.sales-order.detail', [
            'order' => $order->load('konsumen', 'invoice_detail.barang', 'invoice_detail.barangStokHarga', 'invoice_detail.satuan_grosir', 'invoice_detail.barang.satuan'),
            'ppn' => $ppn,
            'penyesuaian' => $penyesuaian,
        ]);
    }

    public function sales_order_delete(InvoiceJualSalesDetail $orderDetail)
    {

        $check = InvoiceJualSalesDetail::where('invoice_jual_sales_id', $orderDetail->invoice_jual_sales_id)->where('deleted', 0)->count();

        if ($check == 1) {
            return redirect()->back()->with('error', 'Item tidak bisa dihapus, karena item ini adalah satu-satunya item dalam sales order ini');
        }

        $orderDetail->update([
            'deleted' => !$orderDetail->deleted,
        ]);

        return redirect()->back()->with('success', 'Item ditandai sebagai dihapus. Silahkan lanjutkan proses untuk menghapus item ini.');
    }

    public function sales_order_update(InvoiceJualSales $order, Request $request)
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

        return redirect()->route('billing.sales-order', ['kas_ppn' => $order->kas_ppn])->with($res['status'], $res['message']);
    }

    public function sales_order_void(InvoiceJualSales $order)
    {
        $db = new InvoiceJualSales;
        $res = $db->order_void($order->id);

        return response()->json($res);
    }

    public function sales_order_lanjutkan(InvoiceJualSales $order)
    {
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        $db = new InvoiceJual();
        $res = $db->lanjut_order($order->id);

        if ($res['status'] == 'success') {

            return redirect()->route('billing.form-jual.invoice', ['invoice' => $res['invoice']->id]);
        }

        return redirect()->back()->with($res['status'], $res['message']);
    }

    public function preorder(Request $request)
    {
        $data = OrderInden::with(['detail.barang.barang_nama', 'detail.barang.satuan', 'konsumen'])->where('is_finished', 0)->get();

        return view('billing.pre-order.index', [
            'data' => $data,
        ]);
    }

    public function preorder_detail(OrderInden $preorder)
    {
        $order = $preorder->load(['detail.barang.barang_nama', 'detail.barang.satuan', 'konsumen.kode_toko', 'karyawan']);

        return view('billing.pre-order.detail', [
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

        return redirect()->route('billing.pre-order')->with($res['status'], $res['message']);
    }

    public function preorder_void(OrderInden $preorder)
    {

        $db = new OrderInden;

        $res = $db->order_void($preorder->id);

        return response()->json($res);

    }

    public function preorder_finish(OrderInden $preorder)
    {

        $preorder->update([
            'is_finished' => 1,
        ]);

        return response()->json(['status' => 'success', 'message' => 'Preorder berhasil diselesaikan']);
    }



    public function form_barang_retur(Request $request)
    {

        $d = $request->validate([
            'tipe' => 'required|in:1,2',
        ]);

        if ($d['tipe'] == 1) {
            return redirect()->back()->with('error', 'Fitur ini masih dalam tahap pengembangan!!');
        }

        $data = BarangRetur::with(['karyawan', 'konsumen.kode_toko'])->where('status', 0)->where('tipe', $d['tipe'])->get();
        $supplier = BarangUnit::select('id', 'nama')->get();
         $sales = Karyawan::with('jabatan')->whereHas('jabatan', function ($query) {
                    $query->where('is_sales', 1);
                })->select('id', 'nama')->get();
        $konsumen = $d['tipe'] == 1 ? null : Konsumen::where('active', 1)
                                            ->with(['kode_toko'])
                                            ->get();

        return view('billing.form-barang-retur.index', [
            'data' => $data,
            'supplier' => $supplier,
            'konsumen' => $konsumen,
            'sales' => $sales,
            'tipe' => $d['tipe'],
        ]);
    }

    public function form_barang_retur_store(Request $request)
    {
        $data = $request->validate([
            'tipe' => 'required|in:1,2',
            'barang_unit_id' => 'required_if:tipe,1|exists:barang_units,id',
            'karyawan_id' => 'required|exists:karyawans,id',
            'konsumen_id' => 'required_if:tipe,2|exists:konsumens,id',
        ]);

        try {
            DB::beginTransaction();
            $db = new BarangRetur;

            $b = $db->create([
                'nomor' => $db->generateNomor(),
                'tipe' => $data['tipe'],
                'barang_unit_id' => $data['barang_unit_id'] ?? null,
                'konsumen_id' => $data['konsumen_id'] ?? null,
                'karyawan_id' => $data['karyawan_id'] ?? null,
            ]);

            DB::commit();

        } catch (\Throwable $th) {

            DB::rollback();

            return redirect()->back()->with('error', 'Gagal Membuat Form Barang Retur, '.$th->getMessage());
        }


        return redirect()->route('billing.form-barang-retur.detail', ['retur' => $b->id]);
    }

    public function form_barang_retur_delete(BarangRetur $retur)
    {
        $retur->delete();

        return redirect()->back()->with('success', 'Data retur berhasil dihapus');
    }

    public function form_barang_retur_detail_datatable(BarangRetur $retur, Request $request)
    {
        $keranjangMap = $retur->details->mapWithKeys(function ($detail) {
            return [
                $detail->barang_id => [
                    'qty' => $detail->qty,
                    'id' => $detail->id // Ini adalah 'barang_retur_detail_id'
                ]
            ];
        });
            // TIPE 2 (Dari Konsumen) -> Tampilkan daftar BARANG (Produk)
        $query = Barang::with(['barang_nama', 'satuan', 'kategori'])
            ->select('barangs.*')
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

                // 2. Cek apakah barang ini ada di map keranjang kita
                if ($keranjangMap->has($barangId)) {

                    // JIKA ADA (Mode Edit)
                    $detail = $keranjangMap->get($barangId);
                    $qty = $detail['qty'];
                    $detailId = $detail['id'];
                    $qtyFormatted = number_format($qty, 0, ',', '.');
                    $satuan = $row->satuan->nama ?? 'PCS';

                    // Buat tombol "Edit" (hijau) yang menampilkan Qty
                    return '<button type="button" class="btn btn-success btn-sm btn-modal-trigger" '.
                        ' data-row=\'' . $rowData . '\' '.
                        ' data-qty="' . $qty . '" '. // <= Kirim Qty
                        ' data-detail-id="' . $detailId . '">'. // <= Kirim Detail ID
                        $qtyFormatted . ' ' . $satuan .
                        '</button>';

                } else {

                    // JIKA TIDAK ADA (Mode Tambah Baru)

                    // Buat tombol "Pilih" (biru) seperti biasa
                    return '<button type="button" class="btn btn-primary btn-sm btn-modal-trigger" '.
                        ' data-row=\'' . $rowData . '\' '.
                        ' data-qty="0" '. // <= Qty adalah 0
                        ' data-detail-id="0">'. // <= Detail ID adalah 0
                        'Pilih'.
                        '</button>';
                }
            })
            ->rawColumns(['nama_barang', 'action'])
            ->make(true);

    }

    public function form_barang_retur_detail(BarangRetur $retur, Request $request)
    {

        $keranjang = $retur->load('karyawan')->details;

        $selectKategori = BarangKategori::all();
        $selectBarangNama = BarangNama::select('id', 'nama')->distinct()->orderBy('id')->get();


        return view('billing.form-barang-retur.detail', [
            'b' => $retur,
            'keranjang' => $keranjang,
            'selectKategori' => $selectKategori,
            'selectBarangNama' => $selectBarangNama,
        ]);
    }

    public function form_barang_retur_detail_empty(BarangRetur $retur)
    {
        $retur->details()->delete();

        return redirect()->back()->with('success', 'Item berhasil dihapus dari daftar retur');
    }

    public function form_barang_retur_detail_store(BarangRetur $retur, Request $request)
    {
        $data = $request->validate([
            'barang_id' => 'required|exists:barang_stok_hargas,id',
            'jumlah' => 'required',
        ]);

        $data['jumlah'] = str_replace('.', '', $data['jumlah']);

        if ($data['jumlah'] < 0) {
            return redirect()->back()->with('error', 'Jumlah Tidak Boleh dibawah 0!');
        }

        $db = new BarangReturDetail;

        // $stok = BarangStokHarga::find($data['barang_stok_harga_id'])->stok;

        // if ($data['jumlah'] > $stok) {
        //     return redirect()->back()->with('error', 'Jumlah retur melebihi stok yang tersedia (Stok: '.$stok.')');
        // }

        if ($data['jumlah'] == 0) {
            $res = $db->where('barang_retur_id', $retur->id)
                ->where('barang_id', $data['barang_id'])
                ->delete();

            $res = ['status' => 'success', 'message' => 'Item berhasil dihapus dari daftar retur'];
        } else {

            $res = $db->updateOrCreate([
                'barang_retur_id' => $retur->id,
                'barang_id' => $data['barang_id'],
            ],[
                'barang_retur_id' => $retur->id,
                'barang_id' => $data['barang_id'],
                'qty' => $data['jumlah'],
            ]);

            $res = ['status' => 'success', 'message' => 'Item berhasil ditambahkan ke daftar retur'];
        }

        return redirect()->back()->with($res['status'], $res['message']);
    }

    public function form_barang_retur_detail_preview(BarangRetur $retur)
    {
        $keranjang = $retur->details->load(['barang.barang_nama', 'barang.satuan']);
        $konsumen = $retur->konsumen_id ? $retur->konsumen->load('kode_toko') : null;

        return view('billing.form-barang-retur.keranjang', [
            'b' => $retur,
            'keranjang' => $keranjang,
            'konsumen' => $konsumen,
        ]);
    }

    public function form_barang_retur_detail_lanjutkan(BarangRetur $retur)
    {
        if ($retur->details->isEmpty()) {
            return redirect()->back()->with('error', 'Daftar retur kosong, silahkan tambahkan item terlebih dahulu');
        }

        $db = new BarangRetur;

        $res = $db->checkout_retur($retur->id);


        if ($res['status'] == 'success') {
            return redirect()->route('billing')->with($res['status'], $res['message']);
        }

        return redirect()->back()->with($res['status'], $res['message']);
    }

    public function form_barang_retur_detail_preview_delete(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|exists:barang_retur_details,id',
        ]);

        BarangReturDetail::find($data['id'])->delete();

        return response()->json(['status' => 'success', 'message' => 'Item berhasil dihapus dari daftar retur']);
    }

    public function barang_retur(Request $request)
    {
        $konsumens = Konsumen::with(['kode_toko'])->where('active', 1)->orderBy('nama', 'asc')->get();
        $barang_units = BarangUnit::orderBy('nama', 'asc')->get(); // Asumsi ini adalah supplier
         $sales = Karyawan::with('jabatan')->whereHas('jabatan', function ($query) {
                    $query->where('is_sales', 1);
                })->select('id', 'nama')->get();

        return view('billing.barang-retur.index', [
            'sales' => $sales,
            'konsumens' => $konsumens,       // Kirim data konsumen ke view
            'barang_units' => $barang_units, // Kirim data barang unit ke view
        ]);
    }

    public function barang_retur_data(Request $request)
    {
        $query = BarangRetur::with(['barang_unit', 'konsumen.kode_toko', 'karyawan' => function($q){
            $q->select('id', 'nama');
        }]);

        // 2. Terapkan Filter dari request AJAX
        if ($request->filled('konsumen_id')) {
            $query->where('konsumen_id', $request->konsumen_id);
        }

        if ($request->filled('barang_unit_id')) {
            $query->where('barang_unit_id', $request->barang_unit_id);
        }

        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }

        if ($request->filled('sales')) {
            $query->where('karyawan_id', $request->sales);
        }

        // Jika filter status tidak diisi, tampilkan yg default (Diajukan & Diproses)
        if ($request->filled('status')) {
            $query->where('barang_returs.status', $request->status);
        } else {
            $query->whereIn('barang_returs.status', [1, 2]);
        }

        // 3. Gunakan DataTables untuk memproses
        return DataTables::of($query)
            ->addIndexColumn() // Menambahkan kolom DT_RowIndex


            // Buat Link di Kode
            ->editColumn('kode', function ($row) {
                $url = route('billing.barang-retur.detail', ['retur' => $row->id]);
                return '<a href="'.$url.'" class="btn btn-primary btn-sm">'.$row->kode.'</a>';
            })

             ->addColumn('sales', function ($row) {
                return $row->karyawan ? $row->karyawan->nama : '-';
            })
            // Ambil Nama Supplier
            ->addColumn('supplier', function ($row) {
                return $row->barang_unit ? $row->barang_unit->nama : '-';
            })

            // Format Nama Konsumen
            ->addColumn('konsumen_nama', function ($row) {
                if (!$row->konsumen) {
                    return '-';
                }
                $kode = $row->konsumen->kode_toko ? $row->konsumen->kode_toko->kode : '';
                return $kode . ' ' . $row->konsumen->nama;
            })
            ->addColumn('status_badge', function($row){
                return $row->status_badge; // Memanggil accessor
            })

            ->addColumn('action', function($row){
                return $row->action; // Memanggil accessor
            })


            // Izinkan HTML di kolom ini
            ->rawColumns(['kode', 'status_badge', 'action'])

            ->make(true);
    }

    public function barang_retur_terima(BarangRetur $retur)
    {
        $res = $retur->terima_retur($retur->id);

        if ($res['status'] == 'success') {
            // Berhasil, siapkan URL untuk PDF baru
            $res['preview_url'] = route('billing.barang-retur.cetak_diterima', $retur->id);
        }

        return response()->json($res);
    }

    public function barang_retur_detail(BarangRetur $retur)
    {
        $detail = $retur->load(['details.barang.satuan', 'details.barang.barang_nama', 'konsumen.kode_toko', 'barang_unit']);

        return view('billing.barang-retur.detail', [
            'data' => $detail,
        ]);
    }

    public function barang_retur_kirim(BarangRetur $retur)
    {
        // return ['status' => 'error', 'message' => 'fitur dalam perbaikan'];
        // Panggil fungsi model yang sudah diubah namanya menjadi 'proses_retur'
        $res = $retur->proses_retur($retur->id);

        if ($res['status'] == 'success') {
            // Berhasil, siapkan URL untuk PDF LAMA (sesuai permintaan)
            $res['preview_url'] = route('billing.barang-retur.cetak', $retur->id);
        }

        return response()->json($res);
    }

    private function hapusPdfRetur(BarangRetur $retur)
    {
        $fileName = 'retur-'.$retur->kode.'.pdf';
        $filePath = 'public/pdf/barang_retur/'.$fileName;

        if (Storage::exists($filePath)) {
            Storage::delete($filePath);
        }
    }

    public function barang_retur_void(BarangRetur $retur)
    {
        // Panggil fungsi 'void_retur' yang logikanya sudah disesuaikan
        $userAllow = ['su', 'admin'];
        if (!in_array(auth()->user()->role, $userAllow )) {
            return response()->json(['status' => 'error', 'message' => "Hanya admin yang boleh void!!"]);
        }

        $res = $retur->void_retur($retur->id);
        return response()->json($res);
    }

    public function barang_retur_selesaikan(BarangRetur $retur)
    {
        // Panggil fungsi 'selesaikan_retur' yang logikanya sudah disesuaikan
        return response()->json(['status' => 'error', 'message' =>'Fitur Sedang Dalam Perbaikan']);
        // $res = $retur->selesaikan_retur($retur->id);
        // return response()->json($res);
    }

    public function barang_retur_cetak(BarangRetur $retur, Request $request)
    {
        $fileName = 'retur-'.$retur->kode.'.pdf';
        $filePath = 'public/pdf/barang_retur/'.$fileName;

        if (!Storage::exists($filePath)) {
            try {
                $retur->load(['details.barang.satuan', 'details.barang.barang_nama', 'konsumen.kode_toko', 'details.barang.unit']);
                $pt = Config::where('untuk', 'resmi' )->first();
                $tanggal = Carbon::parse($retur->waktu_diproses)->format('d-m-Y');

                $pdf = PDF::loadView('billing.barang-retur.pdf', [ // PDF LAMA
                    'data' => $retur,
                    'pt' => $pt,
                    'tanggal' => $tanggal,
                ]);

                Storage::put($filePath, $pdf->output());

            } catch (\Throwable $th) {
                return redirect()->back()->with('error', 'Gagal membuat PDF: '.$th->getMessage());
            }
        }

        // ... (Logika download/inline Anda tetap sama)
        if ($request->has('download')) {
            return Storage::download($filePath, $fileName);
        } else {
            return Storage::response($filePath, $fileName);
        }
    }

    public function barang_retur_cetak_diterima(BarangRetur $retur, Request $request)
    {
        $fileName = 'retur-diterima-'.$retur->kode.'.pdf';
        $filePath = 'public/pdf/barang_retur_diterima/'.$fileName; // Folder baru agar tidak tumpang tindih

        // Buat folder jika belum ada
        if (!Storage::exists('public/pdf/barang_retur_diterima')) {
            Storage::makeDirectory('public/pdf/barang_retur_diterima');
        }

        // PDF ini sebaiknya selalu dibuat ulang (atau hapus file lama jika ada)
        if (Storage::exists($filePath)) {
            Storage::delete($filePath);
        }

        try {
            $retur->load(['details.barang.satuan', 'details.barang.barang_nama', 'konsumen.kode_toko', 'barang_unit']);
            $pt = Config::where('untuk', 'resmi' )->first();
            $tanggal = Carbon::parse($retur->waktu_diterima)->format('d-m-Y');

            // PANGGIL BLADE PDF BARU ANDA
            $pdf = PDF::loadView('billing.barang-retur.pdf-diterima', [ // <-- Nama blade PDF baru Anda
                'data' => $retur,
                'pt' => $pt,
                'tanggal' => $tanggal,
            ]);

            Storage::put($filePath, $pdf->output());

        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Gagal membuat PDF Diterima: '.$th->getMessage());
        }

        if ($request->has('download')) {
            return Storage::download($filePath, $fileName);
        } else {
            return Storage::response($filePath, $fileName);
        }
    }

    public function barang_retur_proses()
    {
        $konsumens = Konsumen::with(['kode_toko'])->where('active', 1)->orderBy('nama', 'asc')->get();
        $barang_units = BarangUnit::orderBy('nama', 'asc')->get(); // Asumsi ini adalah supplier
        $sales = Karyawan::with('jabatan')->whereHas('jabatan', function ($query) {
                    $query->where('is_sales', 1);
                })->select('id', 'nama')->get();
        return view('billing.barang-retur-proses.index', [
            'sales' => $sales,
            'konsumens' => $konsumens,       // Kirim data konsumen ke view
            'barang_units' => $barang_units,
        ]);
    }

    public function stok_retur_data(Request $request)
    {
        if ($request->ajax()) {
            $query = StokRetur::with(['barang.unit', 'barang.kategori', 'barang.satuan', 'barang.barang_nama', 'sources.detail.barang_retur.konsumen'])
                    ->where('total_qty_karantina', '>', 0)
                    ->select('stok_returs.*');

            // --- Logic Filter ---
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

            // Menggunakan Yajra DataTables (Recommended)
            return datatables()->of($query)
                ->addIndexColumn()
                ->addColumn('perusahaan', function($row){
                    return $row->barang->unit->nama ?? '-';
                })
                ->addColumn('kelompok', function($row){
                    return $row->barang->kategori->nama ?? '-';
                })
                ->addColumn('nama_barang', function($row){
                    return $row->barang->barang_nama->nama ?? '-';
                })
                ->addColumn('kode_barang', function($row){
                    return $row->barang->kode ?? '-';
                })
                ->addColumn('merk', function($row){
                    return $row->barang->merk ?? '-';
                })
                ->addColumn('stok_retur', function($row){
                    return '<span class="fw-bold text-danger">'.number_format($row->total_qty_karantina).'</span>';
                })
                ->addColumn('satuan', function($row){
                    return $row->barang->satuan->nama ?? '-';
                })
                ->addColumn('ppn', function($row){
                    // Logika PPN (Sesuaikan dengan kolom database Anda)
                    return ($row->barang->jenis == 1) ? '<span class="badge bg-success">Ya</span>' : '-';
                })
                ->addColumn('non_ppn', function($row){
                    return ($row->barang->jenis == 2) ? '<span class="badge bg-success">Ya</span>' : '-';
                })
                ->addColumn('detail_sumber', function($row){
                    // Tombol Trigger Modal History (Logic sebelumnya)
                    return '<button type="button" class="btn btn-sm btn-info text-white btn-history"
                            data-id="'.$row->id.'"
                            data-nama="'.$row->barang->barang_nama->nama.'">
                            <i class="bi bi-clock-history"></i> Lihat
                            </button>';
                })
                ->addColumn('aksi', function($row){
                    // Data attributes untuk Modal Keranjang
                    return '<button type="button" class="btn btn-primary btn-sm btn-modal-trigger"
                                data-row=\''.json_encode([
                                    'id' => $row->barang_id, // ID Barang untuk keranjang
                                    'stok_retur_id' => $row->id,
                                    'nama' => $row->barang->barang_nama->nama,
                                    'kode' => $row->barang->kode,
                                    'merk' => $row->barang->merk,
                                    'stok' => $row->total_qty_karantina,
                                    'satuan' => $row->barang->satuan
                                ]).'\'
                                data-qty="0"
                                data-detail-id="">
                                <i class="bi bi-cart-plus"></i> Proses
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


}
