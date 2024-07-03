<?php

namespace App\Http\Controllers;

use App\Models\db\CostOperational;
use App\Models\db\Karyawan;
use App\Models\GroupWa;
use App\Models\Investor;
use App\Models\InvestorModal;
use App\Models\KasBesar;
use App\Models\Produksi\RencanaProduksi;
use App\Models\RekapGaji;
use App\Models\RekapGajiDetail;
use App\Models\transaksi\InventarisInvoice;
use App\Models\transaksi\InvoiceBelanja;
use App\Models\transaksi\InvoiceJual;
use App\Services\StarSender;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillingController extends Controller
{
    public function index()
    {
        $np = InvoiceBelanja::where('ppn_masukan', 0)->where('tempo', 0)->count();
        $rp = RencanaProduksi::where('approved', 0)->count();
        $ij = InvoiceJual::where('lunas', 0)->count();

        return view('billing.index', [
            'np' => $np,
            'rp' => $rp,
            'ij' => $ij,
        ]);
    }

    public function nota_ppn_masukan()
    {
        $data = InvoiceBelanja::where('ppn_masukan', 0)->where('tempo', 0)->get();

        return view('billing.ppn-masukan.index', [
            'data' => $data,
        ]);
    }

    public function claim_ppn(InvoiceBelanja $invoice)
    {
        $db = new InvoiceBelanja();

        $store = $db->claim_ppn($invoice);

        return redirect()->back()->with($store['status'], $store['message']);
    }

    public function invoice_jual()
    {
        $data = InvoiceJual::with(['konsumen', 'detail'])->where('lunas', 0)->get();

        return view('billing.invoice-jual.index', [
            'data' => $data,
        ]);
    }

    public function invoice_jual_pelunasan(InvoiceJual $invoice)
    {
        $db = new InvoiceJual();

        $res = $db->pelunasan($invoice->id);

        return redirect()->back()->with($res['status'], $res['message']);
    }

    public function invoice_jual_detail(InvoiceJual $invoice)
    {
        $data = $invoice->detail;

        $groupedData = $data->groupBy(function($item, $key) {
            return $item->product_jadi->product->kategori->id;
        });

        return view('billing.invoice-jual.detail', [
            'groupedData' => $groupedData,
            'invoice' => $invoice->load('konsumen'),

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
        $saldo = $db->saldoTerakhir();

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
            $arrayKasBesar['modal_investor_terakhir'] = $db->modalInvestorTerakhir();
            $arrayKasBesar['nama_rek'] = "Msng2 Karyawan";
            $arrayKasBesar['bank'] = 'BCA';
            $arrayKasBesar['no_rek'] = '-';

            $storeKasBesar = $db->create($arrayKasBesar);

            DB::commit();

        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();

            return redirect()->back()->with('error', 'Gagal Membuat Form Gaji, '.$th->getMessage());
        }



        $group = GroupWa::where('untuk', 'kas-besar')->first();

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
        $hi = InventarisInvoice::where('pembayaran', 2)->where('lunas', 0)->count();

        return view('billing.form-inventaris.index', [
            'hi' => $hi,
        ]);
    }
}
