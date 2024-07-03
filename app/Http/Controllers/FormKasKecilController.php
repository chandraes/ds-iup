<?php

namespace App\Http\Controllers;

use App\Models\GroupWa;
use App\Models\KasBesar;
use App\Models\KasKecil;
use App\Models\PesanWa;
use App\Models\Rekening;
use App\Services\StarSender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormKasKecilController extends Controller
{
    public function masuk()
    {
        $nomor =  str_pad((KasKecil::max('nomor_kode_kas_kecil') + 1), 2, '0', STR_PAD_LEFT);
        $rekening = Rekening::where('untuk', 'kas-kecil')->first();

        return view('billing.form-kas-kecil.masuk', [
            'nomor' => $nomor,
            'rekening' => $rekening,
        ]);
    }

    public function masuk_store()
    {

        $db = new KasKecil();

        $store = $db->masukKasKecil();

        $kb = new KasBesar();
        $saldo = $kb->saldoTerakhir();

        $group = GroupWa::where('untuk', 'kas-besar')->first();
        $pesan =    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                    "*Form Permintaan Kas Kecil*\n".
                    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                    "*KK".sprintf("%02d",$store->nomor_kode_kas_kecil)."*\n\n".
                    "Nilai : *Rp. 1.000.000,-*\n\n".
                    "Ditransfer ke rek:\n\n".
                    "Bank      : ".$store->bank."\n".
                    "Nama    : ".$store->nama_rek."\n".
                    "No. Rek : ".$store->no_rek."\n\n".
                    "==========================\n".
                    "Sisa Saldo Kas Besar : \n".
                    "Rp. ".number_format($saldo, 0, ',', '.')."\n\n".
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

        return redirect()->route('billing')->with('success', 'Data berhasil disimpan');
    }

    public function keluar()
    {
        return view('billing.form-kas-kecil.keluar');
    }

    public function keluar_store(Request $request)
    {
        $data = $request->validate([
            'uraian' => 'required',
            'nominal' => 'required',
            'tipe' => 'required',
            'nama_rek' => 'nullable',
            'bank' => 'nullable',
            'no_rek' => 'nullable',
        ]);

        $db = new KasKecil();
        $data['nominal'] = str_replace('.', '', $data['nominal']);
        $saldo = $db->saldoTerakhir();

        if($saldo < $data['nominal']){
            return redirect()->back()->with('error', 'Saldo tidak mencukupi');
        }

        if($data['tipe'] == '1'){
            $data['nama_rek'] = 'Cash';
            unset($data['bank']);
            unset($data['no_rek']);
        } elseif($data['tipe'] == '2') {
            $data['nama_rek'] = substr($data['nama_rek'], 0, 15);
        }

        unset($data['tipe']);

        DB::beginTransaction();

        $store = $db->keluarKasKecil($data);

        DB::commit();

        $group = GroupWa::where('untuk', 'kas-kecil')->first();

        if ($data['nama_rek'] == 'Cash') {
            $pesan =    "==========================\n".
                        "*Form Pengeluaran Kas Kecil*\n".
                        "==========================\n\n".
                        "Uraian: ".$store->uraian."\n\n".
                        "Nilai : *Rp. ".number_format($store->nominal)."*\n\n".
                        "Cash\n\n".
                        "==========================\n".
                        "Sisa Saldo Kas Kecil : \n".
                        "Rp. ".number_format($store->saldo, 0, ',', '.')."\n\n".
                        "Terima kasih 🙏🙏🙏\n";
        } else {
            $pesan =    "==========================\n".
                        "*Form Pengeluaran Kas Kecil*\n".
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
        }

        $send = new StarSender($group->nama_group, $pesan);
        $res = $send->sendGroup();

        $status = ($res == 'true') ? 1 : 0;

        PesanWa::create([
            'pesan' => $pesan,
            'tujuan' => $group->nama_group,
            'status' => $status,
        ]);


        return redirect()->route('billing')->with('success', 'Data berhasil disimpan');
    }
}
