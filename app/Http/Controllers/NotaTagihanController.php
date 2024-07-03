<?php

namespace App\Http\Controllers;

use App\Models\InvoiceTagihan;
use App\Models\KasBesar;
use App\Models\GroupWa;
use App\Models\Investor;
use App\Models\KasProject;
use App\Models\PesanWa;
use App\Services\StarSender;
use Illuminate\Http\Request;

class NotaTagihanController extends Controller
{
    public function index()
    {
        $data = InvoiceTagihan::with(['customer', 'project','kasProjects', 'invoiceTagihanDetails'])
                                ->where('cutoff', 0)
                                ->where('finished', 0)
                                ->get();

        return view('billing.nota-tagihan.index', [
            'data' => $data,
        ]);
    }

    public function cicilan(InvoiceTagihan $invoice, Request $request)
    {
        $data = $request->validate([
            'nominal' => 'required',
            'uraian' => 'required',
        ]);

        $db = new InvoiceTagihan();
        $kp = new KasProject();

        $store = $db->cicilan($invoice->id, $data);

        $group = GroupWa::where('untuk', 'kas-besar')->first();

        $inv = InvoiceTagihan::where('project_id', $store->project_id)->first();

        $nilai = $inv->nilai_tagihan;
        $profit = $inv->profit;
        $sisa = $kp->sisaTerakhir($store->project_id);
        $ppnMasukan = $inv->ppn_masukan;

        $pesan =    "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n".
                    "*Form Tagihan*\n".
                    "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n\n".
                    "Customer : ".$store->project->customer->singkatan."\n".
                    "Project : *".$store->project->nama."*\n".
                    "Uraian : *".$store->uraian."*\n\n".
                    "Nilai   :  *Rp. ".number_format($store->nominal, 0, ',', '.')."*\n\n".
                    "Ditransfer ke rek:\n\n".
                    "Bank      : ".$store->bank."\n".
                    "Nama    : ".$store->nama_rek."\n".
                    "No. Rek : ".$store->no_rek."\n\n".
                    "==========================\n".
                    "Sisa Saldo Kas Besar : \n".
                    "Rp. ".number_format($store->saldo, 0, ',', '.')."\n\n".
                    "Total Modal Investor : \n".
                    "Rp. ".number_format($store->modal_investor_terakhir, 0, ',', '.')."\n\n".
                    "Total Kas Project : \n".
                    "Rp. ".number_format($sisa, 0, ',', '.')."\n\n".
                    "Total PPn Masukan : \n".
                    "Rp. ".number_format($ppnMasukan, 0, ',', '.')."\n\n".
                    "Nilai Project : \n".
                    "Rp. ".number_format($nilai, 0, ',', '.')."\n\n".
                    "Estimasi Profit Sementara : \n".
                    "Rp. ".number_format($profit, 0, ',', '.')."\n\n".
                    "Terima kasih 🙏🙏🙏\n";

        //Tambahkan sisa tagihan

        $send = new StarSender($group->nama_group, $pesan);
        $res = $send->sendGroup();

        $status = ($res == 'true') ? 1 : 0;

        PesanWa::create([
            'pesan' => $pesan,
            'tujuan' => $group->nama_group,
            'status' => $status,
        ]);

        return redirect()->back()->with('success', 'Cicilan berhasil ditambahkan');


    }

    public function cutoff(InvoiceTagihan $invoice, Request $request)
    {

        $data = $request->validate([
            'estimasi_pembayaran' => 'required',
        ]);

        $store = InvoiceTagihan::cutoff($invoice, $data);

        return redirect()->back()->with($store['status'], $store['message']);

    }

    public function pelunasan(InvoiceTagihan $invoice)
    {
        ini_set('max_execution_time', 180);
        ini_set('memory_limit', '32M');


        $kb = new KasBesar();
        $db = new InvoiceTagihan();

        $saldo = $kb->saldoTerakhir() + $invoice->sisa_tagihan;
        $pengeluaran = (($invoice->kasProjects()->orderBy('id', 'desc')->first()->sisa ?? 0) * -1) + ($invoice->profit > 0 ? $invoice->profit : 0);

        if ($saldo < $pengeluaran) {
            return redirect()->back()->with('error', 'Saldo Kas Besar tidak mencukupi untuk proses pelunasan!');
        }

        $check = Investor::sum('persentase');

        if ($check < 100) {
            return redirect()->back()->with('error', 'Total persentase investor belum mencapai 100%');
        }

        $save = $db->pelunasan($invoice->id);

        return redirect()->back()->with(($save['status'] == 0 ? "error" : "success"), $save['message']);
    }
}
