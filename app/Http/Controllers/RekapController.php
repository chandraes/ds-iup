<?php

namespace App\Http\Controllers;

use App\Models\db\InventarisJenis;
use App\Models\db\InventarisKategori;
use App\Models\db\Konsumen;
use App\Models\GroupWa;
use App\Models\InvestorModal;
use App\Models\KasBesar;
use App\Models\KasKecil;
use App\Models\KasKonsumen;
use App\Models\PesanWa;
use App\Models\RekapGaji;
use App\Models\transaksi\InvoiceBelanja;
use App\Models\transaksi\InvoiceJual;
use App\Services\StarSender;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class RekapController extends Controller
{
    public function index()
    {
        $konsumen = Konsumen::where('active', 1)->get();
        return view('rekap.index', [
            'konsumen' => $konsumen,
        ]);
    }

    public function kas_besar(Request $request)
    {
        $kas = new KasBesar();

        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');

        $dataTahun = $kas->dataTahun();

        $data = $kas->kasBesar($bulan, $tahun);

        $bulanSebelumnya = $bulan - 1;
        $bulanSebelumnya = $bulanSebelumnya == 0 ? 12 : $bulanSebelumnya;
        $tahunSebelumnya = $bulanSebelumnya == 12 ? $tahun - 1 : $tahun;
        $stringBulan = Carbon::createFromDate($tahun, $bulanSebelumnya)->locale('id')->monthName;
        $stringBulanNow = Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;

        $dataSebelumnya = $kas->kasBesarByMonth($bulanSebelumnya, $tahunSebelumnya);

        return view('rekap.kas-besar.index', [
            'data' => $data,
            'dataTahun' => $dataTahun,
            'dataSebelumnya' => $dataSebelumnya,
            'stringBulan' => $stringBulan,
            'tahun' => $tahun,
            'tahunSebelumnya' => $tahunSebelumnya,
            'bulan' => $bulan,
            'stringBulanNow' => $stringBulanNow,
        ]);
    }

    public function kas_besar_print(Request $request)
    {
        $kas = new KasBesar();

        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');

        $data = $kas->kasBesar($bulan, $tahun);

        $bulanSebelumnya = $bulan - 1;
        $bulanSebelumnya = $bulanSebelumnya == 0 ? 12 : $bulanSebelumnya;
        $tahunSebelumnya = $bulanSebelumnya == 12 ? $tahun - 1 : $tahun;
        $stringBulan = Carbon::createFromDate($tahun, $bulanSebelumnya)->locale('id')->monthName;
        $stringBulanNow = Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;

        $dataSebelumnya = $kas->kasBesarByMonth($bulanSebelumnya, $tahunSebelumnya);

        $pdf = PDF::loadview('rekap.kas-besar.pdf', [
            'data' => $data,
            'dataSebelumnya' => $dataSebelumnya,
            'stringBulan' => $stringBulan,
            'tahun' => $tahun,
            'tahunSebelumnya' => $tahunSebelumnya,
            'bulan' => $bulan,
            'stringBulanNow' => $stringBulanNow,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Rekap Kas Besar '.$stringBulanNow.' '.$tahun.'.pdf');
    }


    public function kas_kecil(Request $request)
    {
        $kas = new KasKecil();

        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');

        $dataTahun = $kas->dataTahun();

        $data = $kas->kasKecil($bulan, $tahun);

        $bulanSebelumnya = $bulan - 1;
        $bulanSebelumnya = $bulanSebelumnya == 0 ? 12 : $bulanSebelumnya;
        $tahunSebelumnya = $bulanSebelumnya == 12 ? $tahun - 1 : $tahun;
        $stringBulan = Carbon::createFromDate($tahun, $bulanSebelumnya)->locale('id')->monthName;
        $stringBulanNow = Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;

        $dataSebelumnya = $kas->kasKecilByMonth($bulanSebelumnya, $tahunSebelumnya);

        return view('rekap.kas-kecil.index', [
            'data' => $data,
            'dataTahun' => $dataTahun,
            'dataSebelumnya' => $dataSebelumnya,
            'stringBulan' => $stringBulan,
            'tahun' => $tahun,
            'tahunSebelumnya' => $tahunSebelumnya,
            'bulan' => $bulan,
            'stringBulanNow' => $stringBulanNow,
        ]);
    }

    public function kas_kecil_print(Request $request)
    {
        $kas = new KasKecil();

        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');

        $dataTahun = $kas->dataTahun();

        $data = $kas->kasKecil($bulan, $tahun);

        $bulanSebelumnya = $bulan - 1;
        $bulanSebelumnya = $bulanSebelumnya == 0 ? 12 : $bulanSebelumnya;
        $tahunSebelumnya = $bulanSebelumnya == 12 ? $tahun - 1 : $tahun;
        $stringBulan = Carbon::createFromDate($tahun, $bulanSebelumnya)->locale('id')->monthName;
        $stringBulanNow = Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;

        $dataSebelumnya = $kas->kasKecilByMonth($bulanSebelumnya, $tahunSebelumnya);

        $pdf = PDF::loadview('rekap.kas-kecil.pdf', [
            'data' => $data,
            'dataSebelumnya' => $dataSebelumnya,
            'stringBulan' => $stringBulan,
            'tahun' => $tahun,
            'tahunSebelumnya' => $tahunSebelumnya,
            'bulan' => $bulan,
            'stringBulanNow' => $stringBulanNow,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Rekap Kas Besar '.$stringBulanNow.' '.$tahun.'.pdf');
    }

    public function void_kas_kecil(KasKecil $kas)
    {
        $db = new KasKecil();

        $store = $db->voidKasKecil($kas->id);

        $group = GroupWa::where('untuk', 'kas-kecil')->first();

        $pesan =    "==========================\n".
                    "*Form Void Kas Kecil*\n".
                    "==========================\n\n".
                    "Uraian: ".$store->uraian."\n\n".
                    "Nilai : *Rp. ".number_format($store->nominal)."*\n\n".
                    "Ditransfer ke rek:\n\n".
                    "Bank      : ".$store->bank."\n".
                    "Nama    : ".$store->nama_rek."\n".
                    "No. Rek : ".$store->no_rek."\n\n".
                    "==========================\n".
                    "Sisa Saldo Kas Kecil : \n".
                    "Rp. ".number_format($store->saldo, 0, ',', '.')."\n\n".
                    "Terima kasih 🙏🙏🙏\n";

        $send = new StarSender($group->nama_group, $pesan);
        $res = $send->sendGroup();

        $status = ($res == 'true') ? 1 : 0;

        PesanWa::create([
            'pesan' => $pesan,
            'tujuan' => $group->nama_group,
            'status' => $status,
        ]);

        return redirect()->back()->with('success', 'Data berhasil di void');
    }

    public function rekap_investor()
    {
        $data = InvestorModal::with(['kasBesar' => function ($query) {
                    $query->selectRaw('investor_modal_id, SUM(CASE WHEN jenis = 0 THEN nominal ELSE -nominal END) as total')
                        ->whereNull('modal_investor')
                        ->groupBy('investor_modal_id');
                }])->get();

        return view('rekap.kas-investor.index', [
            'data' => $data,
        ]);
    }

    public function rekap_investor_show(InvestorModal $investor)
    {
        return view('rekap.kas-investor.detail', ['investor' => $investor]);
    }

    public function rekap_investor_detail(InvestorModal $investor, Request $request)
    {
        if ($request->ajax()) {
            $length = $request->get('length'); // Get the requested number of records

            // Define the columns for sorting
            $columns = ['created_at', 'uraian', 'nominal'];

            $query = $investor->load('kasBesar')->kasBesar()->whereNotNull('modal_investor')->orderBy('created_at', 'desc');

            // Handle the sorting
            if ($request->has('order')) {
                $columnIndex = $request->get('order')[0]['column']; // Get the index of the sorted column
                $sortDirection = $request->get('order')[0]['dir']; // Get the sort direction
                $column = $columns[$columnIndex]; // Get the column name

                $query->orderBy($column, $sortDirection);
            }

            $data = $query->paginate($length); // Use the requested number of records

            $data->getCollection()->transform(function ($d) use (&$total) {
                if ($d->jenis == 1) {
                    $total += $d->nominal;
                } else {
                    $total -= $d->nominal;
                    $d->nominal = '-' . $d->nominal; // Add "-" sign when jenis is 0
                }

                if (empty($d->uraian)) {
                    $d->uraian = "Deposit"; // Render kode_deposit when uraian is empty
                }

                return $d;
            });

            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => $data->total(),
                'recordsFiltered' => $data->total(),
                'data' => $data->items(),
                'total' => $total,
            ]);
        }

        return abort(404);
    }

    public function rekap_investor_detail_deviden_show(InvestorModal $investor)
    {
        return view('rekap.kas-investor.detail-deviden', ['investor' => $investor]);
    }

    public function rekap_investor_detail_deviden(InvestorModal $investor, Request $request)
    {
        if ($request->ajax()) {
            $length = $request->get('length'); // Get the requested number of records

            // Define the columns for sorting
            $columns = ['created_at', 'uraian', 'nominal'];

            $query = $investor->load('kasBesar')->kasBesar()->whereNull('modal_investor')->with('project')->orderBy('created_at', 'desc');

            // Handle the sorting
            if ($request->has('order')) {
                $columnIndex = $request->get('order')[0]['column']; // Get the index of the sorted column
                $sortDirection = $request->get('order')[0]['dir']; // Get the sort direction
                $column = $columns[$columnIndex]; // Get the column name

                $query->orderBy($column, $sortDirection);
            }

            $data = $query->paginate($length); // Use the requested number of records

            $data->getCollection()->transform(function ($d) use (&$total) {
                if ($d->jenis == 1) {
                    $total -= $d->nominal;
                    $d->nominal = '-' . $d->nominal;
                } else {
                    $total += $d->nominal;
                     // Add "-" sign when jenis is 0
                }

                $d->project_nama = $d->project->nama ?? '';

                return $d;
            });

            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => $data->total(),
                'recordsFiltered' => $data->total(),
                'data' => $data->items(),
                'total' => $total,
            ]);
        }

        return abort(404);
    }

    public function detail_belanja(InvoiceBelanja $invoice)
    {
        return view('rekap.kas-besar.detail-belanja', [
            'data' => $invoice->load(['rekap', 'rekap.bahan_baku', 'rekap.satuan', 'rekap.bahan_baku.kategori', 'rekap.kemasan.satuan', 'rekap.packaging.satuan']),
        ]);
    }

    public function konsumen(Request $request)
    {
        $data = $request->validate([
            'konsumen_id' => 'required|exists:konsumens,id',
        ]);

        $kas = new KasKonsumen();

        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');

        $dataTahun = $kas->dataTahun();

        $konsumen = Konsumen::find($data['konsumen_id']);

        $data = $kas->kas($data['konsumen_id'],$bulan, $tahun);

        $bulanSebelumnya = $bulan - 1;
        $bulanSebelumnya = $bulanSebelumnya == 0 ? 12 : $bulanSebelumnya;
        $tahunSebelumnya = $bulanSebelumnya == 12 ? $tahun - 1 : $tahun;
        $stringBulan = Carbon::createFromDate($tahun, $bulanSebelumnya)->locale('id')->monthName;
        $stringBulanNow = Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;

        $dataSebelumnya = $kas->kasByMonth($konsumen->id,$bulanSebelumnya, $tahunSebelumnya);

        return view('rekap.kas-konsumen.index', [
            'data' => $data,
            'konsumen' => $konsumen,
            'dataTahun' => $dataTahun,
            'dataSebelumnya' => $dataSebelumnya,
            'stringBulan' => $stringBulan,
            'tahun' => $tahun,
            'tahunSebelumnya' => $tahunSebelumnya,
            'bulan' => $bulan,
            'stringBulanNow' => $stringBulanNow,
        ]);

    }

    public function invoice_penjualan(Request $request)
    {
        $kas = new InvoiceJual();

        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');

        $dataTahun = $kas->dataTahun();
        $data = $kas->rekapInvoice($bulan, $tahun);

        $bulanSebelumnya = $bulan - 1;
        $bulanSebelumnya = $bulanSebelumnya == 0 ? 12 : $bulanSebelumnya;
        $tahunSebelumnya = $bulanSebelumnya == 12 ? $tahun - 1 : $tahun;
        $stringBulan = Carbon::createFromDate($tahun, $bulanSebelumnya)->locale('id')->monthName;
        $stringBulanNow = Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;

        $dataSebelumnya = $kas->rekapInvoiceByMonth($bulanSebelumnya, $tahunSebelumnya);

        return view('rekap.invoice-jual.index', [
            'data' => $data,
            'dataTahun' => $dataTahun,
            'dataSebelumnya' => $dataSebelumnya,
            'stringBulan' => $stringBulan,
            'tahun' => $tahun,
            'tahunSebelumnya' => $tahunSebelumnya,
            'bulan' => $bulan,
            'stringBulanNow' => $stringBulanNow,
        ]);
    }

    public function invoice_penjualan_detail(InvoiceJual $invoice)
    {
        $data = $invoice->detail;

        $groupedData = $data->groupBy(function($item, $key) {
            return $item->product_jadi->product->kategori->id;
        });

        return view('rekap.invoice-jual.detail', [
            'groupedData' => $groupedData,
            'invoice' => $invoice->load('konsumen'),

        ]);
    }

    public function pph_masa(Request $request)
    {
        $kas = new InvoiceJual();

        $tahun = $request->tahun ?? date('Y');

        $dataTahun = $kas->dataTahun();

        $reports = InvoiceJual::selectRaw('
                MONTH(created_at) as month,
                SUM(total) as total_dpp,
                SUM(pph) as total_pph
            ')
            ->groupBy('month')
            ->orderBy('month')
            ->whereYear('created_at', $tahun)
            ->where('lunas', 1)
            ->get();

        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $data = [];
        $grandTotalDpp = 0;
        $grandTotalPph = 0;

        foreach ($months as $num => $name) {
            $report = $reports->firstWhere('month', $num);
            $totalDpp = $report ? $report->total_dpp : 0;
            $totalPph = $report ? $report->total_pph : 0;

            $data[] = [
                'bulan_angka' => $num,
                'bulan' => $name,
                'total_dpp' => $report ? $report->total_dpp : 0,
                'total_pph' => $report ? $report->total_pph : 0,
            ];

            $grandTotalDpp += $totalDpp;
            $grandTotalPph += $totalPph;
        }

         // Urutkan data berdasarkan bulan
         usort($data, function ($a, $b) use ($months) {
            return array_search($a['bulan'], array_values($months)) - array_search($b['bulan'], array_values($months));
        });

        return view('rekap.pph-masa.index', [
            'data' => $data,
            'dataTahun' => $dataTahun,
            'tahun' => $tahun,
            'grandTotalDpp' => $grandTotalDpp,
            'grandTotalPph' => $grandTotalPph,
        ]);
    }

    public function pph_masa_detail(int $month, int $year)
    {
        $kas = new InvoiceJual();

        $data = $kas->whereMonth('created_at',$month)->whereYear('created_at',$year)->where('lunas', 1)->get();

        return view('rekap.pph-masa.detail', [
            'data' => $data,
            'month' => $month,
            'year' => $year,
        ]);
    }

    public function gaji_detail(Request $request)
    {
        $v = $request->validate([
            'bulan' => 'required',
            'tahun' => 'required',
        ]);

        $data = RekapGaji::with('details')->where('bulan', $v['bulan'])->where('tahun', $v['tahun'])->first();

        if (!$data) {
            return redirect()->back()->with('error', 'Data tidak ditemukan!!');
        }

        $bulan = Carbon::createFromDate($v['tahun'], $v['bulan'])->locale('id')->monthName;
        $tahun = $v['tahun'];

        return view('rekap.gaji.detail', [
            'data' => $data,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'bulan_angka' => $v['bulan'],
        ]);
    }

    public function pph_badan(Request $request)
    {
        $db = new InvoiceJual();

        $tahun = $request->tahun ?? date('Y');

        $kelebihan = $request->kelebihan_pph ?? 0;
        $kelebihan = str_replace('.', '', $kelebihan);

        $nfKelebihan = number_format($kelebihan, 2, ',', '.');


        $dataTahun = $db->dataTahun();

        $data = $db->pphBadan($tahun, $kelebihan);
        if ($data['omset'] == 0) {
            return redirect()->back()->with('error', 'Belum ada omset!!');
        }
        return view('rekap.pph-badan.index', [
            'data' => $data,
            'dataTahun' => $dataTahun,
            'tahun' => $tahun,
            'kelebihan' => $kelebihan,
        ]);
    }

    public function inventaris()
    {
        $data = InventarisKategori::has('jenis.rekap')->with('jenis.rekap')->get();

        return view('rekap.inventaris.index', [
            'data' => $data,
        ]);

    }

    public function inventaris_detail(InventarisJenis $jenis)
    {
        $data = $jenis->rekap;

        return view('rekap.inventaris.detail', [
            'data' => $data,
            'inventaris' => $jenis->load('kategori'),
        ]);
    }
}
