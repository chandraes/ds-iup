<?php

namespace App\Http\Controllers;

use App\Models\db\Barang\BarangKategori;
use App\Models\db\Barang\BarangNama;
use App\Models\db\Barang\BarangStokHarga;
use App\Models\db\Barang\BarangType;
use App\Models\db\Barang\BarangUnit;
use App\Models\db\CostOperational;
use App\Models\db\Karyawan;
use App\Models\db\Kreditor;
use App\Models\db\Pajak;
use App\Models\GantiRugi;
use App\Models\GroupWa;
use App\Models\Investor;
use App\Models\InvestorModal;
use App\Models\KasBesar;
use App\Models\Pengelola;
use App\Models\RekapGaji;
use App\Models\RekapGajiDetail;
use App\Models\transaksi\InventarisInvoice;
use App\Models\transaksi\InvoiceBelanja;
use App\Models\transaksi\InvoiceJual;
use App\Models\transaksi\KeranjangJual;
use App\Services\StarSender;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillingController extends Controller
{

    public function lihat_stok(Request $request)
    {
         // $kategori = BarangKategori::with(['barang_nama'])->get();
        // $type = BarangType::with(['unit', 'barangs'])->get();
        $ppnRate = Pajak::where('untuk', 'ppn')->first()->persen;

        $unitFilter = $request->input('unit');
        $typeFilter = $request->input('type');
        $kategoriFilter = $request->input('kategori');
        $barangNamaFilter = $request->input('barang_nama');

        if (!empty($unitFilter) && $unitFilter != '') {
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

        $db = new BarangStokHarga();

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
        $ik = InvoiceJual::where('lunas', 0)->where('void', 0)->where('kas_ppn', 1)->count();
        $ikn = InvoiceJual::where('lunas', 0)->where('void', 0)->where('kas_ppn', 0)->count();
        $gr = GantiRugi::where('lunas', 0)->count();

        return view('billing.index', [
            'is' => $is,
            'ik' => $ik,
            'isn' => $isn,
            'ikn' => $ikn,
            'gr' => $gr,
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

        $db = new KasBesar();

        $store = $db->ppn_masuk_susulan($data['nominal']);

        return redirect()->back()->with($store['status'], $store['message']);

    }

    public function cost_operational()
    {
        $data = CostOperational::all();

        if($data->isEmpty()) {
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

        $db = new KasBesar();

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
        ini_set('max_execution_time', 300); //300 seconds = 5 minutes
        ini_set('memory_limit', '512M');

        $ds = $request->validate([
            'total' => 'required',
        ]);

        $data = Karyawan::where('status', 1)->get();


        $db = new KasBesar();
        $saldo = $db->saldoTerakhir(1);

        if ($saldo < $ds['total']) {
            return redirect()->back()->with('error', 'Saldo Kas Besar Tidak Cukup');
        }
        try {
            DB::beginTransaction();
            $rekap = RekapGaji::create([
                'uraian' => "Gaji Bulan ".date('F')." Tahun ".date('Y'),
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
                    'nik' => $d->kode.sprintf("%03d", $d->nomor),
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

            $arrayKasBesar['uraian'] = "Gaji Bulan ".date('F')." ".date('Y');
            $arrayKasBesar['tanggal'] = date('Y-m-d');
            $arrayKasBesar['nominal'] = $ds['total'];
            $arrayKasBesar['jenis'] = 0;
            $arrayKasBesar['saldo'] = $saldo - $ds['total'];
            $arrayKasBesar['modal_investor_terakhir'] = $db->modalInvestorTerakhir(1);
            $arrayKasBesar['nama_rek'] = "Msng2 Karyawan";
            $arrayKasBesar['bank'] = 'BCA';
            $arrayKasBesar['no_rek'] = '-';
            $arrayKasBesar['ppn_kas'] = 1;
            $storeKasBesar = $db->create($arrayKasBesar);

            DB::commit();

        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();

            return redirect()->back()->with('error', 'Gagal Membuat Form Gaji, '.$th->getMessage());
        }



        $group = GroupWa::where('untuk', 'kas-besar-ppn')->first();

        $pesan =    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                    "*FORM GAJI KARYAWAN*\n".
                    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                    "Nilai :  *Rp. ".number_format($ds['total'], 0, ',', '.')."*\n\n".
                    "Ditransfer ke rek:\n\n".
                    "Nama     : Masing2 Karyawan\n\n".
                    "==========================\n".
                    "Sisa Saldo Kas Besar : \n".
                    "Rp. ".number_format($storeKasBesar->saldo, 0, ',', '.')."\n\n".
                    "Total Modal Investor : \n".
                    "Rp. ".number_format($storeKasBesar->modal_investor_terakhir, 0, ',', '.')."\n\n".
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

        if ($pengelola->count() == 0 || $investor->count() == 0){
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

        $db = new KasBesar();

        $res = $db->dividen($data);

        return redirect()->route('billing')->with($res['status'], $res['message']);
    }

    public function ganti_rugi(Request $request)
    {
        $data = GantiRugi::with(['barang_stok_harga.barang.satuan','barang_stok_harga.barang.barang_nama', 'karyawan'])->where('lunas', 0)
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
        $db = new GantiRugi();

        $res = $db->void($rugi->id);

        return redirect()->back()->with($res['status'], $res['message']);
    }

    public function ganti_rugi_bayar(GantiRugi $rugi, Request $request)
    {
        $data = $request->validate([
            'jenis' => 'required',
            'nominal' => 'required_if:jenis,1',
        ]);

        $db = new GantiRugi();

        $res = $db->bayar($rugi->id, $data);

        return redirect()->back()->with($res['status'], $res['message']);
    }

    public function bunga_investor(Request $request)
    {
        $data = $request->validate([
            'kas_ppn' => 'required|boolean'
        ]);

        $kreditor = Kreditor::where('is_active', 1)->get();

        if($kreditor->isEmpty()) {
            return redirect()->route('db.kreditor')->with('error', 'Data kreditor kosong, silahkan tambahkan data kreditor terlebih dahulu');
        }
        $db = new KasBesar();
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

        $db = new KasBesar();

        $res = $db->bunga_investor($data);

        return redirect()->route('billing')->with($res['status'], $res['message']);

    }
}
