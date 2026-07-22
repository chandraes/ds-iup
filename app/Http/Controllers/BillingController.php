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
use App\Models\db\Supplier;
use App\Models\GantiRugi;
use App\Models\GroupWa;
use App\Models\Investor;
use App\Models\InvestorModal;
use App\Models\KasBesar;
use App\Models\KasKonsumen;
use App\Models\MetodeBayar;
use App\Models\Pajak\RekapPpn;
use App\Models\Pengaturan;
use App\Models\Pengelola;
use App\Models\PpnKeluaran;
use App\Models\PpnMasukan;
use App\Models\RekapGaji;
use App\Models\RekapGajiDetail;
use App\Models\Rekening;
use App\Models\ReturSupplier;
use App\Models\StokRetur;
use App\Models\transaksi\InventarisInvoice;
use App\Models\transaksi\InvoiceBelanja;
use App\Models\transaksi\InvoiceJual;
use App\Models\transaksi\InvoiceJualSales;
use App\Models\transaksi\InvoiceJualSalesDetail;
use App\Models\transaksi\JanjiBayar;
use App\Models\transaksi\JanjiBayarDetail;
use App\Models\transaksi\JanjiBayarKeranjang;
use App\Models\transaksi\KeranjangBeli;
use App\Models\transaksi\KeranjangJual;
use App\Models\transaksi\OrderInden;
use App\Models\transaksi\OrderIndenDetail;
use App\Models\UangGantung;
use App\Models\User;
use App\Models\Wilayah;
use App\Services\StarSender;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

        $keranjang = KeranjangJual::where('user_id', Auth::user()->id)->get();

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

        $sales_order_all = InvoiceJualSales::where('is_finished', 0)
            ->count();

        $gr = GantiRugi::where('lunas', 0)->count();

        $br = BarangRetur::whereIn('status', [1,2])->count();
        $sr = StokRetur::where('status', 0)->count();
        $ps = ReturSupplier::where('tipe', '<', 3)->count();
        $ug = UangGantung::where('lunas', 0)->where('void', 0)->count();
        $jb = JanjiBayar::where('status', 0)->count();

        $asistenAdm = User::where('role', 'asisten-admin')->select('id', 'name')->withCount('keranjangBeli')->get();
        $sumKeranjangBeli = $asistenAdm->sum('keranjang_beli_count');

        return view('billing.index', [
            'is' => $is,
            'ug' => $ug,
            'jb' => $jb,
            'ps' => $ps,
            'ik' => $invoiceJualCounts->ik,
            'isn' => $isn,
            'ikn' => $invoiceJualCounts->ikn,
            'gr' => $gr,
            'br' => $br,
            'ikt' => $invoiceJualCounts->ikt,
            'iktn' => $invoiceJualCounts->iktn,
            'sr' => $sr,
            'sales_order_all' => $sales_order_all,
            'asistenAdm' => $asistenAdm,
            'sumKeranjangBeli' => $sumKeranjangBeli
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
            'karyawan_id' => 'nullable|exists:karyawans,id',
            'kelompok_rute' => 'nullable|exists:kelompok_rutes,id',
        ]);

        $data = InvoiceJualSales::with(['karyawan', 'konsumen.kode_toko', 'konsumen.kecamatan'])->where('is_finished', 0);

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

        // if ($d['tipe'] == 1) {
        //     return redirect()->back()->with('error', 'Fitur ini masih dalam tahap pengembangan!!');
        // }

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
            'karyawan_id' => 'required_if:tipe,2|exists:karyawans,id',
            'konsumen_id' => 'required_if:tipe,2|exists:konsumens,id',
        ],[
            // 2. Definisikan Kustom Pesan Error di Sini
            'tipe.required' => 'Pilihan tipe wajib diisi.',
            'tipe.in' => 'Tipe yang dipilih tidak valid (harus dari supplier atau dari konsumen).',

            'barang_unit_id.required_if' => 'Supplier wajib diisi jika tipe yang dipilih adalah 1.',
            'barang_unit_id.exists' => 'Supplier yang Anda pilih tidak terdaftar.',

            'karyawan_id.required_if' => 'Sales wajib diisi jika tipe retur yang dipilih adalah dari konsumen.',
            'karyawan_id.exists' => 'Data sales tidak ditemukan.',

            'konsumen_id.required_if' => 'Konsumen wajib diisi jika tipe retur yang dipilih adalah dari konsumen.',
            'konsumen_id.exists' => 'Data konsumen tidak ditemukan.',
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

        if($retur->tipe == 1) {
            $query->where('barang_unit_id', $retur->barang_unit_id);
        }

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

        $keranjang = $retur->load(['karyawan', 'barang_unit'])->details;

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

        if ($retur->tipe == 1) {
            $stok = BarangStokHarga::where('barang_id', $data['barang_id'])->where('stok', '>', 0)->sum('stok');

            if ($data['jumlah'] > $stok) {
                return redirect()->back()->with('error', 'Jumlah retur melebihi stok yang tersedia (Stok: '.$stok.')');
            }
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
        $supplier = $retur->barang_unit_id ? $retur->barang_unit : null;

        return view('billing.form-barang-retur.keranjang', [
            'b' => $retur,
            'keranjang' => $keranjang,
            'konsumen' => $konsumen,
            'supplier' => $supplier,
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

        if($retur->tipe == 2) {
            $res = $retur->proses_retur($retur->id);
        } else {
            $res = $retur->proses_retur_supplier($retur->id);
        }

        if ($res['status'] == 'success' && $retur->tipe == 2) {
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
        if (!in_array(Auth::user()->role, $userAllow )) {
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

    public function otorisasi_pembelian(Request $request)
    {
        $data = $request->validate([
            'asistenId' => 'required|exists:users,id'
        ]);

        $user = User::find($data['asistenId']);
        if ($user->role != 'asisten-admin') {
            return redirect()->back()->with('error', 'User ini bukan user Asisten Admin!!');
        }

        $keranjang = KeranjangBeli::where('user_id', $user->id)
                    ->withCount('details')
                    ->withSum('details', 'total')
                    ->get();

        // dd($keranjang);

        $ppnRate = Pajak::where('untuk', 'ppn')->first()->persen;

        return view('billing.otorisasi-beli.index', [
            'data' => $keranjang,
            'user' => $user,
            'ppnRate' => $ppnRate,
        ]);
    }

    public function otorisasi_pembelian_keranjang(KeranjangBeli $keranjang)
    {
        $supplier = Supplier::where('barang_unit_id', $keranjang->barang_unit_id)->first();

        if (!$supplier) {
            $message = Auth::user()->role != 'asisten-admin' ? 'Perusahaan ini belum di atur Suppliernya. Silahkan atur terlebih dahulu di Menu Database Supplier!' :
                                            'Perusahaan ini belum di atur Suppliernya. Silahkan hubungi admin untuk mengisi data di Menu Database Supplier!';
            return redirect()->back()->with('error', $message);
        }

        $jatuhTempo = $supplier->pembayaran == 2 ? Carbon::now()->addDays($supplier->tempo_hari)->format('d-m-Y') : '';

        $ppnRate = Pajak::where('untuk', 'ppn')->first()->persen;

        $asistenId = $keranjang->user_id;

        return view('billing.otorisasi-beli.keranjang', [
            'b' => $keranjang,
            'keranjang' => $keranjang->details,
            'supplier' => $supplier,
            'ppnRate' => $ppnRate,
            'jatuhTempo' => $jatuhTempo,
            'asistenId' => $asistenId
         ]);
    }

    public function otorisasi_pembelian_keranjang_checkout(Request $request)
    {
        $data = $request->validate([
            'asistenId' => 'required|exists:users,id',
            'kas_ppn' => 'required',
            'tempo' => 'required',
            'supplier_id' => 'required',
            'uraian' => 'required',
            'diskon' => 'required',
            'add_fee' => 'required',
            'jenis' => 'required',
            'dp' => 'required_if:tempo,1',
            'dp_ppn' => 'nullable',
            'jatuh_tempo' => 'required_if:tempo,1',
        ]);

        $db = new KeranjangBeli();

        $res = $db->checkout_otorisasi($data);

        if ($res['status'] != 'success') {
            return redirect()->back()->with($res['status'], $res['message']);
        }

        return redirect()->route('billing')->with($res['status'], $res['message']);
    }

    public function uang_gantung_form()
    {
        return view('billing.uang-gantung.form');
    }

    public function uang_gantung_form_store(Request $request)
    {
        $data = $request->validate([
            'ppn_kas' => 'required|boolean',
            'tanggal' => 'required|date',
            'keterangan' => 'required',
            'nominal' => 'required',
        ]);

        $data['user_id'] = Auth::id();

        $db = new KasBesar;

        $res = $db->uang_gantung_create($data);

        if ($res['status'] != 'success') {
            return redirect()->back()->with($res['status'], $res['message']);
        }

        return redirect()->route('billing')->with($res['status'], $res['message']);


    }

    public function uang_gantung_data(Request $request)
    {
          if ($request->ajax()) {
            $query = UangGantung::with(['user'])->where('lunas', 0)->where('void', 0);

            // --- Logic Filter ---
            if ($request->has('ppn_kas') && $request->ppn_kas != '') {
                $query->where('ppn_kas', $request->ppn_kas);
            }

            // Menggunakan Yajra DataTables (Recommended)
            return datatables()->of($query)
                ->addIndexColumn()
                ->addColumn('status_kas', function($row){
                    // Logika PPN (Sesuaikan dengan kolom database Anda)
                    return ($row->ppn_kas == 1) ? '<span class="badge bg-success">KAS PPN</span>' : '<span class="badge bg-warning">KAS NON PPN</span>';
                })
                ->addColumn('tanggal_input', function($row){
                    return Carbon::parse($row->created_at)->format('Y-m-d');
                })
                ->addColumn('aksi', function($row){
                    $btn = '';
                    $role = ['su', 'admin'];

                    if (!in_array(Auth::user()->role, $role)) {
                        return '-';
                    }
                    // Asumsi: jika lunas = 0, tampilkan tombol Selesaikan. Jika sudah 1, tampilkan teks Lunas.
                    if ($row->lunas == 0) {
                        $btn .= '<button type="button" class="btn btn-success btn-sm me-1 btn-selesaikan" data-id="'.$row->id.'">
                                    <i class="fa fa-check-circle"></i> Selesaikan
                                </button>';
                    // Tombol Void (Hapus)
                    $btn .= '<button type="button" class="btn btn-danger btn-sm btn-void" data-id="'.$row->id.'">
                                <i class="fa fa-trash"></i> Void
                            </button>';
                    }
                    return $btn;
                })
                ->rawColumns(['status_kas', 'aksi'])
                ->make(true);
        }
    }

    public function uang_gantung()
    {
        return view('billing.uang-gantung.index');
    }

    public function uang_gantung_lunas($id)
    {
        $db = new KasBesar;

        $res = $db->uang_gantung_lunas($id);

        return response()->json($res);
    }

    public function uang_gantung_void(Request $request, $id)
    {
        // 1. Validasi inputan dari sisi server
        $request->validate([
            'alasan' => 'required|string'
        ]);

        // 2. Akses nilai alasan
        $alasan_void = $request->alasan;

        $db = new KasBesar;

        $res = $db->uang_gantung_void($id, $alasan_void);

        return response()->json($res);
    }

    public function form_janji_bayar(Request $request)
    {
        $ppn = Pajak::where('untuk', 'ppn')->first()->persen;
        $sales = Karyawan::with('jabatan')->whereHas('jabatan', function ($query) {
                    $query->where('is_sales', 1);
                })->select('id', 'nama')->get();

        $kecamatanIds = Konsumen::distinct()->pluck('kecamatan_id')->filter()->all();
        $kabupatenIds = Konsumen::distinct()->pluck('kabupaten_kota_id')->filter()->all();

        $kabupaten = Wilayah::whereIn('id', $kabupatenIds)->get();
        $kecamatan = Wilayah::whereIn('id', $kecamatanIds)
                    ->when(
                        ($request->has('kabupaten_id') && $request->kabupaten_id != ''),
                        function ($query) use ($request) {
                            $wilayah = Wilayah::find($request->kabupaten_id)->id_wilayah;
                            return $query->where('id_induk_wilayah', $wilayah);
                        }
                    )->get();


        return view('billing.form-janji-bayar.index', [
            'ppn' => $ppn,
            'sales' => $sales,
            'kecamatan' => $kecamatan,
            'kabupaten' => $kabupaten,
        ]);
    }

   public function form_janji_bayar_data(Request $request)
    {
        if ($request->ajax()) {
            $filters = $request->only(['expired', 'apa_ppn', 'karyawan_id', 'kecamatan_id', 'kabupaten_id']);

            $query = InvoiceJual::janjiBayar($filters);

            // Hitung grand total server-side
            $totalsQuery = clone $query;
            $totals = $totalsQuery->select(
                DB::raw('SUM(total) as sum_total'),
                DB::raw('SUM(diskon) as sum_diskon'),
                DB::raw('SUM(ppn) as sum_ppn'),
                DB::raw('SUM(add_fee) as sum_add_fee'),
                DB::raw('SUM(grand_total) as sum_grand_total'),
                DB::raw('SUM(dp) as sum_dp'),
                DB::raw('SUM(dp_ppn) as sum_dp_ppn'),
                DB::raw('SUM(sisa_ppn) as sum_sisa_ppn'),
                DB::raw('SUM(sisa_tagihan) as sum_sisa_tagihan')
            )->first();

            $query->with(['karyawan', 'konsumen.kabupaten_kota', 'konsumen.kecamatan', 'konsumen.kode_toko', 'invoice_jual_cicil']);

            // ==========================================
            // OPTIMASI SERVER-LIGHT: Tarik Data Keranjang Aktif User
            // ==========================================
            $userId = Auth::id();
            $cartItems = JanjiBayarKeranjang::where('user_id', $userId)->get();
            $inCartIds = $cartItems->pluck('invoice_jual_id')->toArray();

            $currentCartKonsumen = null;
            if ($cartItems->isNotEmpty()) {
                $firstItem = $cartItems->first();
                $currentCartKonsumen = $firstItem->konsumen ? $firstItem->konsumen->kode_toko?->kode .' '.$firstItem->konsumen->nama : 'Konsumen Terpilih';
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->filterColumn('sales', function($query, $keyword) {
                    $query->whereHas('karyawan', function($q) use ($keyword) {
                        $q->where('nama', 'LIKE', "%{$keyword}%");
                    });
                })
                ->filterColumn('konsumen_kode', function($query, $keyword) {
                    $query->whereHas('konsumen', function($q) use ($keyword) {
                        $q->where('kode', 'LIKE', "%{$keyword}%");
                    });
                })
                ->filterColumn('konsumen_nama', function($query, $keyword) {
                    $query->whereHas('konsumen', function($q) use ($keyword) {
                        $q->where('nama', 'LIKE', "%{$keyword}%");
                    });
                })
                ->filterColumn('konsumen_plafon', function($query, $keyword) {
                    $query->whereHas('konsumen', function($q) use ($keyword) {
                        $q->where('plafon', 'LIKE', "%{$keyword}%");
                    });
                })
                ->filterColumn('daerah', function($query, $keyword) {
                    $query->whereHas('konsumen.kabupaten_kota', function($q) use ($keyword) {
                        $q->where('nama_wilayah', 'LIKE', "%{$keyword}%");
                    })->orWhereHas('konsumen.kecamatan', function($q) use ($keyword) {
                        $q->where('nama_wilayah', 'LIKE', "%{$keyword}%");
                    });
                })
                ->orderColumn('tanggal_en', function ($query, $order) {
                    $query->orderBy('created_at', $order);
                })
                ->orderColumn('sales', function ($query, $order) {
                    $query->orderBy('karyawan_id', $order);
                })
                ->orderColumn('konsumen_kode', function ($query, $order) {
                    $query->orderBy(
                        Konsumen::select('kode')->whereColumn('konsumens.id', 'invoice_juals.konsumen_id'), $order
                    );
                })
                ->orderColumn('konsumen_nama', function ($query, $order) {
                    $query->orderBy(
                        Konsumen::select('nama')->whereColumn('konsumens.id', 'invoice_juals.konsumen_id'), $order
                    );
                })
                ->orderColumn('konsumen_plafon', function ($query, $order) {
                    $query->orderBy(
                        Konsumen::select('plafon')->whereColumn('konsumens.id', 'invoice_juals.konsumen_id'), $order
                    );
                })
                ->addColumn('sales', function ($row) {
                    return $row->karyawan ? $row->karyawan->nama : '';
                })
                ->addColumn('daerah', function ($row) {
                    $kab = $row->konsumen->kabupaten_kota ? $row->konsumen->kabupaten_kota->nama_wilayah . ', ' : '';
                    $kec = $row->konsumen->kecamatan ? $row->konsumen->kecamatan->nama_wilayah : '';
                    return $kab . $kec;
                })
                ->addColumn('konsumen_kode', function ($row) {
                    return $row->konsumen->full_kode;
                })
                ->addColumn('konsumen_nama', function ($row) {
                    return ($row->konsumen->kode_toko ? $row->konsumen->kode_toko->kode . ' ' : '') . $row->konsumen->nama;
                })
                ->addColumn('nota_data', function ($row) {
                    return [
                        'url' => route('billing.invoice-konsumen.detail', $row->id),
                        'kode' => $row->kode
                    ];
                })
                ->addColumn('nilai_data', function ($row) {
                    return [
                        'dpp' => $row->dpp,
                        'diskon' => $row->nf_diskon,
                        'ppn' => $row->nf_ppn,
                        'add_fee' => $row->nf_add_fee
                    ];
                })
                ->addColumn('cicilan_data', function ($row) {
                    $cicilan = $row->invoice_jual_cicil ? $row->invoice_jual_cicil->sum('nominal') + $row->invoice_jual_cicil->sum('ppn') : 0;
                    $history = [];
                    if ($row->invoice_jual_cicil) {
                        foreach ($row->invoice_jual_cicil as $index => $c) {
                            $history[] = [
                                'no' => $index + 1,
                                'tanggal' => $c->tanggal ?? '-',
                                'nominal' => number_format($c->nominal + $c->ppn, 0, ',', '.')
                            ];
                        }
                    }
                    return [
                        'total_formatted' => number_format($cicilan, 0, ',', '.'),
                        'history' => $history,
                        'kode_nota' => $row->kode
                    ];
                })

                // ==========================================
                // UBAH BAGIAN ACTION_DATA: Untuk Status Keranjang
                // ==========================================
                ->addColumn('action_data', function ($row) use ($inCartIds) {
                    return [
                        'id' => $row->id,
                        'konsumen_id' => $row->konsumen_id,
                        'in_cart' => in_array($row->id, $inCartIds), // O(1) Lookup Array cepat
                        'sisa_tagihan' => $row->nf_sisa_tagihan,
                        'ppn_dipungut' => $row->ppn_dipungut,
                        'nf_sisa_ppn' => $row->nf_sisa_ppn
                    ];
                })
                ->with([
                    'totals' => [
                        'dpp' => number_format($totals->sum_total ?? 0, 0, ',', '.'),
                        'diskon' => number_format($totals->sum_diskon ?? 0, 0, ',', '.'),
                        'ppn' => number_format($totals->sum_ppn ?? 0, 0, ',', '.'),
                        'add_fee' => number_format($totals->sum_add_fee ?? 0, 0, ',', '.'),
                        'grand_total' => number_format($totals->sum_grand_total ?? 0, 0, ',', '.'),
                        'dp' => number_format($totals->sum_dp ?? 0, 0, ',', '.'),
                        'dp_ppn' => number_format($totals->sum_dp_ppn ?? 0, 0, ',', '.'),
                        'sisa_ppn' => number_format($totals->sum_sisa_ppn ?? 0, 0, ',', '.'),
                        'sisa_tagihan' => number_format($totals->sum_sisa_tagihan ?? 0, 0, ',', '.'),
                    ],
                    // Kirim metadata keranjang ter-update ke DataTables Frontend
                    'cart_meta' => [
                        'count' => count($inCartIds),
                        'konsumen_nama' => $currentCartKonsumen
                    ]
                ])
                ->make(true);
        }
    }

    public function form_janji_bayar_keranjang()
    {
        $userId = Auth::id();

        // Tarik semua item keranjang milik user ini beserta relasi invoice dan konsumennya
        $cartItems = JanjiBayarKeranjang::with(['invoice_jual.konsumen'])
            ->where('user_id', $userId)
            ->get();

        // Jika keranjang kosong, kembalikan ke halaman utama dengan pesan warning
        if ($cartItems->isEmpty()) {
            return redirect()->route('billing.form-janji-bayar.index')->with('error', 'Keranjang Anda masih kosong.');
        }

        // Ambil data konsumen dari item pertama (karena konsumen dipastikan sama melalui validasi Opsi B)
        $konsumen = $cartItems->first()->invoice_jual->konsumen;

        // Hitung total sisa tagihan dari semua invoice di keranjang
        $totalSisaTagihan = 0;
        foreach ($cartItems as $item) {
            $totalSisaTagihan += $item->invoice_jual->sisa_tagihan;
        }

        $metodeBayars = MetodeBayar::all();

        return view('billing.form-janji-bayar.keranjang', compact('cartItems', 'konsumen', 'totalSisaTagihan', 'metodeBayars'));
    }

    public function form_janji_bayar_tambah_keranjang(Request $request)
    {
        $invoice = InvoiceJual::findOrFail($request->invoice_jual_id);
        $userId = Auth::id();

        // Validasi Opsi B: Cek apakah sudah ada konsumen lain yang mengisi keranjang user ini
        $existingCart = JanjiBayarKeranjang::where('user_id', $userId)->first();

        if ($existingCart && $existingCart->konsumen_id != $invoice->konsumen_id) {
            $namaKonsumenAktif = $existingCart->konsumen ? $existingCart->konsumen->kode_toko?->kode . ' '. $existingCart->konsumen->nama : 'Konsumen Lain';
            return response()->json([
                'status' => 'error',
                'code' => 'DIFFERENT_CONSUMER',
                'message' => "Keranjang Anda sedang mengunci invoice milik [{$namaKonsumenAktif}]."
            ], 400);
        }

        // Input data aman dari duplikasi
        JanjiBayarKeranjang::firstOrCreate([
            'user_id' => $userId,
            'invoice_jual_id' => $invoice->id,
        ], [
            'konsumen_id' => $invoice->konsumen_id
        ]);

        return response()->json(['status' => 'success', 'message' => 'Berhasil dimasukkan ke keranjang.']);
    }

    // 2. Aksi Hapus Item Tertentu dari Keranjang
    public function form_janji_bayar_hapus_keranjang(Request $request)
    {
        JanjiBayarKeranjang::where('user_id', Auth::id())
            ->where('invoice_jual_id', $request->invoice_jual_id)
            ->delete();

        return response()->json(['status' => 'success']);
    }

    // 3. Aksi Kosongkan Seluruh isi Keranjang User
    public function form_janji_bayar_kosongkan_keranjang()
    {
        JanjiBayarKeranjang::where('user_id', Auth::id())->delete();
        return response()->json(['status' => 'success']);
    }

    // 4. Eksekusi Checkout Akhir Menyimpan ke Tabel JanjiBayar & Detail
   public function form_janji_bayar_checkout(Request $request)
    {
        $userId = Auth::id();
        $cartItems = JanjiBayarKeranjang::where('user_id', $userId)->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('billing.form-janji-bayar')->with('error', 'Keranjang belanja Anda kosong.');
        }

        // SANITASI INPUT NOMINAL
        if ($request->has('nominal')) {
            $cleanNominal = str_replace('.', '', $request->nominal);
            $request->merge(['nominal' => (float) $cleanNominal]);
        }

        // Hitung ulang sisa tagihan asli dari DB untuk validasi keamanan
        $inCartIds = $cartItems->pluck('invoice_jual_id')->toArray();
        $totalSisaTagihanCart = InvoiceJual::whereIn('id', $inCartIds)->sum('sisa_tagihan');

        $metodeBayarConfig = MetodeBayar::where('slug', $request->metode)->first();

        // Default fallback jika tidak ditemukan (misal 30 hari)
        $maxDays = $metodeBayarConfig ? $metodeBayarConfig->max_hari : 30;
        $maxDateAllowed = now()->addDays($maxDays)->format('Y-m-d');

        $request->validate([
            'kode' => 'required|unique:janji_bayars,kode',
            'metode' => 'required|exists:metode_bayars,slug', // Validasi terhadap database tabel baru
            'nominal' => 'required|numeric|min:' . $totalSisaTagihanCart,
            'jatuh_tempo' => 'required|date|after_or_equal:today|before_or_equal:' . $maxDateAllowed,
        ], [
            'nominal.min' => 'Nominal janji bayar tidak boleh lebih kecil dari total sisa tagihan (Rp ' . number_format($totalSisaTagihanCart, 0, ',', '.') . ').',
            'kode.unique' => 'Kode unik ini sudah terdaftar di database.',
            'jatuh_tempo.before_or_equal' => 'Tanggal jatuh tempo untuk metode ' . ($metodeBayarConfig ? $metodeBayarConfig->nama : '') . ' maksimal tanggal ' . \Carbon\Carbon::parse($maxDateAllowed)->format('d-m-Y') . ' (+' . $maxDays . ' hari).',
        ]);

        $konsumenId = $cartItems->first()->konsumen_id;

        DB::beginTransaction();
        try {
            // 1. Simpan ke JanjiBayar
            $janjiBayar = JanjiBayar::create([
                'kode' => $request->kode,
                'konsumen_id' => $konsumenId,
                'metode' => $request->metode,
                'nominal' => $request->nominal,
                'jatuh_tempo' => $request->jatuh_tempo,
                'status' => 0,
            ]);

            // 2. Simpan ke JanjiBayarDetail (Bulk Insert Optimasi)
            $details = [];
            foreach ($cartItems as $item) {
                $details[] = [
                    'janji_bayar_id' => $janjiBayar->id,
                    'invoice_jual_id' => $item->invoice_jual_id,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
            JanjiBayarDetail::insert($details);

            // OPTIMASI: Update status lunas sekaligus menggunakan whereIn (Menghindari N+1 Query)
            InvoiceJual::whereIn('id', $inCartIds)->update(['lunas' => 1]);

            // 3. Kosongkan Keranjang
            JanjiBayarKeranjang::where('user_id', $userId)->delete();

            $dbKasKonsumen = new KasKonsumen;
            $sisaTerakhir = $dbKasKonsumen->sisaTerakhir($konsumenId);
            $penguranganSisa = $sisaTerakhir - $request->nominal;

            if($penguranganSisa < 0) {
                $penguranganSisa = 0; // Pastikan tidak menjadi negatif
            }

            $dbKasKonsumen->create([
                'konsumen_id' => $konsumenId,
                'janji_bayar_id' => $janjiBayar->id,
                'uraian' => 'Janji Bayar - ' . $janjiBayar->kode,
                'bayar' => $request->nominal,
                'sisa' => $penguranganSisa,
            ]);

            DB::commit();
            return redirect()->route('billing.form-janji-bayar')->with('success', 'Data Janji Bayar berhasil dieksekusi dan disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses data: ' . $e->getMessage())->withInput();
        }
    }

    public function invoice_janji_bayar()
    {
        return view('billing.invoice-janji-bayar.index');
    }

    public function invoice_janji_bayar_data(Request $request)
    {
       if ($request->ajax()) {
            // PERUBAHAN 1: Filter query agar HANYA mengambil status = 0 (Pending)
            $query = JanjiBayar::with(['konsumen.kode_toko'])
                ->where('status', 0)
                ->select('janji_bayars.*');

            return DataTables::of($query)
                ->addIndexColumn()

                ->editColumn('nominal', function ($row) {
                    return 'Rp ' . number_format($row->nominal, 0, ',', '.');
                })

                ->editColumn('jatuh_tempo', function ($row) {
                    return \Carbon\Carbon::parse($row->jatuh_tempo)->format('d-m-Y');
                })

                ->addColumn('konsumen_nama', function ($row) {
                    if (!$row->konsumen) return '-';
                    $kode = $row->konsumen->kode_toko ? $row->konsumen->kode_toko->kode . ' ' : '';
                    return $kode . $row->konsumen->nama;
                })

                // Menyesuaikan tampilan status berdasarkan nilai 0
                ->editColumn('status', function ($row) {
                    if ($row->status == 0) {
                        return '<span class="badge bg-warning text-dark px-2 py-1"><i class="fa fa-clock-o"></i> Pending</span>';
                    }
                    return '<span class="badge bg-success px-2 py-1"><i class="fa fa-check"></i> Selesai</span>';
                })

                // PERUBAHAN 2: Validasi Role untuk Tombol Void di Sisi Server
               ->addColumn('action', function ($row) {
                    $btn = '<a href="' . route('billing.invoice-janji-bayar.detail', $row->id) . '" class="btn btn-sm btn-info text-white px-3 shadow-sm me-1">' .
                        '<i class="fa fa-eye"></i> Detail</a>';

                    // TAMBAHAN: Tombol Selesai
                    $btn .= '<button type="button" class="btn btn-sm btn-success px-3 shadow-sm btn-complete-document me-1" data-id="' . $row->id . '" data-kode="' . $row->kode . '">' .
                        '<i class="fa fa-check-circle"></i> Selesai</button>';

                    // Cek hak akses user untuk tombol Void
                    if (Auth::check() && in_array(Auth::user()->role, ['admin', 'su'])) {
                        $btn .= '<button type="button" class="btn btn-sm btn-danger px-3 shadow-sm btn-void-document" data-id="' . $row->id . '" data-kode="' . $row->kode . '">' .
                            '<i class="fa fa-ban"></i> Void</button>';
                    }

                    return $btn;
                })

                ->filterColumn('konsumen_nama', function ($query, $keyword) {
                    $query->whereHas('konsumen', function ($q) use ($keyword) {
                        $q->where('nama', 'LIKE', "%{$keyword}%");
                    });
                })

                ->rawColumns(['status', 'action'])
                ->make(true);
        }
    }

    public function invoice_janji_bayar_detail($id)
    {
        $janjiBayar = JanjiBayar::with(['konsumen.kode_toko'])->findOrFail($id);

        $details = JanjiBayarDetail::with(['invoice_jual'])
            ->where('janji_bayar_id', $id)
            ->get();

        return view('billing.invoice-janji-bayar.detail', compact('janjiBayar', 'details'));
    }

    public function invoice_janji_bayar_void($id)
    {
        if (!Auth::check() || !in_array(Auth::user()->role, ['admin', 'su'])) {
            return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki hak akses untuk melakukan void.'], 403);
        }

        DB::beginTransaction();
        try {
            $janjiBayar = JanjiBayar::findOrFail($id);

            // Ambil semua ID invoice jual yang terikat dengan dokumen janji bayar ini
            $invoiceIds = JanjiBayarDetail::where('janji_bayar_id', $id)->pluck('invoice_jual_id')->toArray();

            if (!empty($invoiceIds)) {
                // Kembalikan status lunas menjadi 0 pada tabel invoice_juals
                InvoiceJual::whereIn('id', $invoiceIds)->update(['lunas' => 0]);
            }

            $janjiBayar->update(['status' => 99]);

            $dbKasKonsumen = new KasKonsumen;
            $sisaTerakhir = $dbKasKonsumen->sisaTerakhir($janjiBayar->konsumen_id);
            $penambahanSisa = $sisaTerakhir + $janjiBayar->nominal;

            $dbKasKonsumen->create([
                'konsumen_id' => $janjiBayar->konsumen_id,
                'janji_bayar_id' => $janjiBayar->id,
                'uraian' => 'Void Janji Bayar - ' . $janjiBayar->kode,
                'sisa' => $penambahanSisa,
            ]);

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Dokumen janji bayar ' . $janjiBayar->kode . ' berhasil di-void. Status invoice dikembalikan.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Gagal melakukan void: ' . $e->getMessage()], 500);
        }
    }

    public function invoice_janji_bayar_complete(JanjiBayar $id)
    {
        $janjiBayar = $id;

        if ($janjiBayar->status !== 0) {
            return response()->json(['status' => 'error', 'message' => 'Hanya dokumen dengan status Pending yang dapat diselesaikan.']);
        }

        $dbInv = new InvoiceJual;
        $kas = new KasBesar;

        // Ambil data statis (rekening & group WA) bisa tetap di luar transaction
        $rekenings = Rekening::whereIn('untuk', ['kas-besar-ppn', 'kas-besar-non-ppn'])->get()->keyBy('untuk');
        $groupWas = GroupWa::whereIn('untuk', ['kas-besar-ppn', 'kas-besar-non-ppn'])->get()->keyBy('untuk');

        $waNotifications = [];

        // =================================================================
        // 1. BLOK PROSES TRANSAKSI DATABASE (SEKARANG DENGAN PESSIMISTIC LOCK)
        // =================================================================
        DB::beginTransaction();
        try {

            // AMANKAN DI SINI: Pindahkan pencarian saldo ke dalam Transaction + lockForUpdate()
            // Ini akan mengunci baris terakhir kas besar agar user lain mengantre
            $lastKasPpn = KasBesar::where('ppn_kas', 1)->orderBy('id', 'desc')->lockForUpdate()->first();
            $saldoPpn = $lastKasPpn?->saldo ?? 0;
            $modalPpn = $lastKasPpn?->modal_investor_terakhir ?? 0;

            $lastKasNonPpn = KasBesar::where('ppn_kas', 0)->orderBy('id', 'desc')->lockForUpdate()->first();
            $saldoNonPpn = $lastKasNonPpn?->saldo ?? 0;
            $modalNonPpn = $lastKasNonPpn?->modal_investor_terakhir ?? 0;

            $details = $janjiBayar->details()->with('invoice_jual')->get();

            foreach ($details as $detail) {
                $inv = $detail->invoice_jual;
                if ($inv) {
                    $kas_ppn = $inv->ppn > 0 ? 1 : 0;
                    $kasMana = $kas_ppn == 1 ? 'kas-besar-ppn' : 'kas-besar-non-ppn';

                    if ($kas_ppn == 1) {
                        $dbInv->store_ppn($inv->id, $inv->sisa_ppn);
                    }

                    $sisa_tagihan = (float) str_replace('.', '', $inv->sisa_tagihan);
                    $rekening = $rekenings->get($kasMana);

                    // Update running balance dengan aman di memori
                    if ($kas_ppn == 1) {
                        $saldoPpn += $sisa_tagihan;
                        $currentSaldo = $saldoPpn;
                        $currentModal = $modalPpn;
                    } else {
                        $saldoNonPpn += $sisa_tagihan;
                        $currentSaldo = $saldoNonPpn;
                        $currentModal = $modalNonPpn;
                    }

                    $store = $kas->create([
                        'invoice_jual_id' => $inv->id,
                        'ppn_kas' => $kas_ppn,
                        'uraian' => 'Pelunasan '.$inv->kode,
                        'jenis' => '1',
                        'nominal' => $sisa_tagihan,
                        'saldo' => $currentSaldo,
                        'nama_rek' => $rekening?->nama_rek,
                        'no_rek' => $rekening?->no_rek,
                        'bank' => $rekening?->bank,
                        'modal_investor_terakhir' => $currentModal,
                    ]);

                    $waNotifications[] = [
                        'kasMana' => $kasMana,
                        'kas_ppn' => $kas_ppn,
                        'uraian' => $store->uraian,
                        'saldo' => $store->saldo,
                        'nominal' => $store->nominal,
                        'bank' => $store->bank,
                        'nama_rek' => $store->nama_rek,
                        'no_rek' => $store->no_rek,
                    ];
                }
            }

            $janjiBayar->update(['status' => 1]);

            DB::commit(); // Selesai commit, kunci (lock) dilepas otomatis untuk digunakan user berikutnya

        } catch (\Exception $e) {
            DB::rollBack(); // Jika gagal, kunci juga dilepas dan data aman
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyelesaikan dokumen janji bayar: ' . $e->getMessage()
            ], 500);
        }
        // =================================================================
        // 2. BLOK PROSES INTEGRASI WHATSAPP (TERISOLASI DI LUAR TRANSACTION)
        // =================================================================
        if (!empty($waNotifications)) {
            try {
                // Query agregat berat dijalankan di luar transaction dan cukup 1 kali saja
                $getKas = $kas->getKas();
                $saldoTerakhirPpn = (new RekapPpn())->saldoTerakhir();
                $ppnMasukan = (new PpnMasukan())->where('is_finish', 0)->sum('nominal') + $saldoTerakhirPpn;
                $ppnKeluaran = (new PpnKeluaran())->where('is_expired', 0)->where('is_finish', 0)->sum('nominal');

                foreach ($waNotifications as $notif) {
                    try {
                        $kasMana = $notif['kasMana'];
                        $kas_ppn = $notif['kas_ppn'];
                        $group = $groupWas->get($kasMana)?->nama_group;

                        if (!$group) continue;

                        if ($kas_ppn == 1) {
                            $addPesan = "Sisa Saldo Kas Besar: \n".
                                        'Rp. '.number_format($notif['saldo'], 0, ',', '.')."\n\n".
                                        "Total Modal Investor PPN: \n".
                                        'Rp. '.number_format($kas->modalInvestorTerakhir(1), 0, ',', '.')."\n\n";
                        } else {
                            $addPesan = "Sisa Saldo Kas Besar: \n".
                                        'Rp. '.number_format($notif['saldo'], 0, ',', '.')."\n\n".
                                        "Total Modal Investor Non PPN: \n".
                                        'Rp. '.number_format($getKas['modal_investor_non_ppn'], 0, ',', '.')."\n\n";
                        }

                        $pesan = "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n".
                                    "*PELUNASAN JUAL BARANG*\n".
                                    "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n\n".
                                    'Uraian :  *'.$notif['uraian']."*\n\n".
                                    'Nilai    :  *Rp. '.number_format($notif['nominal'], 0, ',', '.')."*\n\n".
                                    "Ditransfer ke rek:\n\n".
                                    'Bank      : '.$notif['bank']."\n".
                                    'Nama    : '.$notif['nama_rek']."\n".
                                    'No. Rek : '.$notif['no_rek']."\n\n".
                                    "==========================\n".
                                    $addPesan.
                                    "Total PPn Masukan : \n".
                                    'Rp. '.number_format($ppnMasukan, 0, ',', '.')."\n\n".
                                    "Total PPn Keluaran : \n".
                                    'Rp. '.number_format($ppnKeluaran, 0, ',', '.')."\n\n".
                                    "Terima kasih 🙏🙏🙏\n";

                        // Panggil API WA 3rd Party
                        $kas->sendWa($group, $pesan);

                    } catch (\Exception $waEx) {
                        // Mencegah looping WA terhenti jika salah satu pesan gagal terkirim/gagal simpan ke model PesanWa
                        Log::error('Gagal memproses salah satu pesan WhatsApp Pelunasan Janji Bayar (Uraian: '.$notif['uraian'].'): ' . $waEx->getMessage());
                    }
                }
            } catch (\Exception $waAgregatEx) {
                // Menangkap error jika query agregat di atas (getKas, sum PPN) bermasalah agar tidak merusak response sukses
                Log::error('Gagal memproses data agregat WhatsApp pada Pelunasan Janji Bayar ID '. $janjiBayar->id .': ' . $waAgregatEx->getMessage());
            }
        }

        // Selalu kembalikan response sukses karena status database sudah resmi berubah menjadi Selesai (1)
        return response()->json([
            'status' => 'success',
            'message' => 'Dokumen janji bayar ' . $janjiBayar->kode . ' berhasil diselesaikan dengan sukses.'
        ]);
    }
}
