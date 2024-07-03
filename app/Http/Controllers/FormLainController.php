<?php

namespace App\Http\Controllers;

use App\Models\Rekening;
use App\Models\KasBesar;
use App\Models\GroupWa;
use App\Models\Pengaturan;
use App\Models\PesanWa;
use App\Services\StarSender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormLainController extends Controller
{
    public function masuk()
    {
        $rekening = Rekening::where('untuk', 'kas-besar')->first();
        $batasan = Pengaturan::where('untuk', 'form-lain-lain')->first()->nilai;

        return view('billing.lain-lain.masuk', [
            'rekening' => $rekening,
            'batasan' => $batasan,
        ]);
    }

    public function masuk_store(Request $request)
    {
        $data = $request->validate([
            'uraian' => 'required',
            'nominal' => 'required',
        ]);

        $data['nominal'] = str_replace('.', '', $data['nominal']);

        $role = ['admin', 'su'];

        $db = new KasBesar;

        if (!in_array(auth()->user()->role, $role)){
            $batasan = Pengaturan::where('untuk', 'form-lain-lain')->first()->nilai;

            if ($data['nominal'] > $batasan) {
                return redirect()->back()->with('error', 'Nominal Melebihi Batasan yang Ditentukan!!');
            }
        }

        $res = $db->lainMasuk($data);

        return redirect()->route('billing')->with($res['status'], $res['message']);

    }

    public function keluar()
    {
        $batasan = Pengaturan::where('untuk', 'form-lain-lain')->first()->nilai;

        return view('billing.lain-lain.keluar', [
            'batasan' => $batasan,
        ]);
    }

    public function keluar_store(Request $request)
    {
        $data = $request->validate([
            'uraian' => 'required',
            'nominal' => 'required',
            'nama_rek' => 'required',
            'no_rek' => 'required',
            'bank' => 'required',
        ]);

        $data['nominal'] = str_replace('.', '', $data['nominal']);
        
        $role = ['admin', 'su'];

        if (!in_array(auth()->user()->role, $role)){
            $batasan = Pengaturan::where('untuk', 'form-lain-lain')->first()->nilai;

            if ($data['nominal'] > $batasan) {
                return redirect()->back()->with('error', 'Nominal Melebihi Batasan yang Ditentukan!!');
            }
        }
        $db = new KasBesar;
        $saldo = $db->saldoTerakhir();

        if ($saldo < $data['nominal']) {
            return redirect()->back()->with('error', 'Saldo Tidak Mencukupi');
        }

        DB::beginTransaction();

        $store = $db->lainKeluar($data);

        $group = GroupWa::where('untuk', 'kas-besar')->first();

        $pesan ="🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                "*Form Lain2 (Dana Keluar)*\n".
                 "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                 "Uraian :  ".$data['uraian']."\n".
                 "Nilai :  *Rp. ".number_format($store->nominal, 0, ',', '.')."*\n\n".
                 "Ditransfer ke rek:\n\n".
                "Bank      : ".$store->bank."\n".
                "Nama    : ".$store->nama_rek."\n".
                "No. Rek : ".$store->no_rek."\n\n".
                "==========================\n".
                "Sisa Saldo Kas Besar : \n".
                "Rp. ".number_format($store->saldo, 0, ',', '.')."\n\n".
                "Total Modal Investor : \n".
                "Rp. ".number_format($store->modal_investor_terakhir, 0, ',', '.')."\n\n".
                "Terima kasih 🙏🙏🙏\n";

        $send = new StarSender($group->nama_group, $pesan);
        $res = $send->sendGroup();


        $status = ($res == 'true') ? 1 : 0;

        PesanWa::create([
            'pesan' => $pesan,
            'tujuan' => $group->nama_group,
            'status' => $status,
        ]);

        DB::commit();

        return redirect()->route('billing')->with('success', 'Data Berhasil Ditambahkan');

    }
}
