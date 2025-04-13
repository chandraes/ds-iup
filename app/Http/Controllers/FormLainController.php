<?php

namespace App\Http\Controllers;

use App\Models\db\Pajak;
use App\Models\GroupWa;
use App\Models\KasBesar;
use App\Models\Pengaturan;
use App\Models\PesanWa;
use App\Models\PpnMasukan;
use App\Services\StarSender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormLainController extends Controller
{
    public function masuk()
    {
        $batasan = Pengaturan::where('untuk', 'form-lain-lain')->first()->nilai;

        return view('billing.lain-lain.masuk', [
            'batasan' => $batasan,
        ]);
    }

    public function masuk_store(Request $request)
    {
        $data = $request->validate([
            'uraian' => 'required',
            'nominal' => 'required',
            'ppn_kas' => 'required',
        ]);

        $data['nominal'] = str_replace('.', '', $data['nominal']);

        $role = ['admin', 'su'];

        $db = new KasBesar;

        if (! in_array(auth()->user()->role, $role)) {
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
        $ppnRate = Pajak::where('untuk', 'ppn')->first()->persen;

        return view('billing.lain-lain.keluar', [
            'batasan' => $batasan,
            'ppnRate' => $ppnRate,
        ]);
    }

    public function keluar_store(Request $request)
    {
        $commonRules = [
            'uraian' => 'required',
            'nominal' => 'required',
            'nama_rek' => 'required',
            'no_rek' => 'required',
            'bank' => 'required',
            'ppn_kas' => 'required',
        ];

        $role = ['admin', 'su'];

        if (in_array(auth()->user()->role, $role)) {
            $commonRules['apa_ppn'] = 'required_if:ppn_kas,1';
        }

        $data = $request->validate($commonRules);

        $data['nominal'] = str_replace('.', '', $data['nominal']);

        $role = ['admin', 'su'];

        if (! in_array(auth()->user()->role, $role)) {
            $batasan = Pengaturan::where('untuk', 'form-lain-lain')->first()->nilai;

            if ($data['nominal'] > $batasan) {
                return redirect()->back()->with('error', 'Nominal Melebihi Batasan yang Ditentukan!!');
            }
        }

        $db = new KasBesar;

        $saldo = $db->saldoTerakhir($data['ppn_kas']);

        if ($saldo < $data['nominal']) {
            return redirect()->back()->with('error', 'Saldo Tidak Mencukupi');
        }

        try {
            DB::beginTransaction();

            if (auth()->user()->role == 'admin' && $data['apa_ppn'] == 1) {
                $ppnRate = Pajak::where('untuk', 'ppn')->first()->persen;
                $ppnNominal = $data['nominal'] * $ppnRate / 100;

                PpnMasukan::create([
                    'uraian' => $data['uraian'],
                    'nominal' => $ppnNominal,
                    'saldo' => (new PpnMasukan)->saldoTerakhir() + $ppnNominal,
                ]);

                $data['nominal'] += $ppnNominal;
            }

            // This line is now outside and after the conditional block, so it's executed regardless of the condition.
            $store = $db->lainKeluar($data);

            DB::commit();

            $kasPpn = [
                'saldo' => $db->saldoTerakhir(1),
                'modal_investor' => $db->modalInvestorTerakhir(1),
            ];

            $kasNonPpn = [
                'saldo' => $db->saldoTerakhir(0),
                'modal_investor' => $db->modalInvestorTerakhir(0),
            ];

            if ($data['ppn_kas'] == 1) {
                $addPesan = "Sisa Saldo Kas Besar PPN: \n".
                            'Rp. '.number_format($kasPpn['saldo'], 0, ',', '.')."\n\n".
                            "Total Modal Investor PPN: \n".
                            'Rp. '.number_format($kasPpn['modal_investor'], 0, ',', '.')."\n\n";
            } else {
                $addPesan = "Sisa Saldo Kas Besar Non PPN: \n".
                            'Rp. '.number_format($kasNonPpn['saldo'], 0, ',', '.')."\n\n".
                            "Total Modal Investor Non PPN: \n".
                            'Rp. '.number_format($kasNonPpn['modal_investor'], 0, ',', '.')."\n\n";
            }

            // sum modal investor
            $totalModal = $kasPpn['modal_investor'] + $kasNonPpn['modal_investor'];

            $groupName = $data['ppn_kas'] == 1 ? 'kas-besar-ppn' : 'kas-besar-non-ppn';

            $group = GroupWa::where('untuk', $groupName)->first();

            $pesan = "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                    "*Form Lain2 (Dana Keluar)*\n".
                    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                    'Uraian :  '.$data['uraian']."\n".
                    'Nilai :  *Rp. '.number_format($store->nominal, 0, ',', '.')."*\n\n".
                    "Ditransfer ke rek:\n\n".
                    'Bank      : '.$store->bank."\n".
                    'Nama    : '.$store->nama_rek."\n".
                    'No. Rek : '.$store->no_rek."\n\n".
                    "==========================\n".
                    $addPesan.
                    "Terima kasih 🙏🙏🙏\n";

            $send = new StarSender($group->nama_group, $pesan);
            $res = $send->sendGroup();

            $storeWa = PesanWa::create([
                'pesan' => $pesan,
                'tujuan' => $group->nama_group,
                'status' => 0,
            ]);

            if ($res == 'true') {
                $storeWa->update(['status' => 1]);
            }

            return redirect()->route('billing')->with('success', 'Data Berhasil Ditambahkan');
        } catch (\Throwable $th) {
            // throw $th;
            DB::rollback();

            return redirect()->back()->with('error', 'Gagal Menambahkan Data, '.$th->getMessage());
        }

    }
}
