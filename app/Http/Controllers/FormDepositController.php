<?php

namespace App\Http\Controllers;

use App\Models\Rekening;
use App\Models\InvestorModal;
use App\Models\KasBesar;
use Illuminate\Http\Request;

class FormDepositController extends Controller
{
    public function masuk()
    {

        $rekening = Rekening::where('untuk', 'kas-besar')->first();
        $kode = str_pad((KasBesar::max('nomor_deposit') + 1), 2, '0', STR_PAD_LEFT);
        $investor = InvestorModal::all();

        return view('billing.form-deposit.masuk', [
            'rekening' => $rekening,
            'kode' => $kode,
            'investor' => $investor,
        ]);
    }

    public function masuk_store(Request $request)
    {
        $data = $request->validate([
            'nominal' => 'required',
            'investor_modal_id' => 'required|exists:investor_modals,id',
        ]);

        $db = new KasBesar();

        $store = $db->deposit($data);

        return redirect()->route('billing')->with($store['status'], $store['message']);
    }

    public function keluar()
    {
        $investor = InvestorModal::all();

        return view('billing.form-deposit.keluar', [
            'investor' => $investor,
        ]);
    }

    public function getModalInvestorProject(Request $request)
    {
        
    }

    public function keluar_store(Request $request)
    {
        $data = $request->validate([
            'nominal' => 'required',
            'investor_modal_id' => 'required|exists:investor_modals,id',
        ]);

        $db = new KasBesar();
        $modal = $db->modalInvestorTerakhir() * -1;
        $saldo = $db->saldoTerakhir();

        $data['nominal'] = str_replace('.', '', $data['nominal']);

        if($modal < $data['nominal'] || $saldo < $data['nominal']){
            return redirect()->back()->with('error', 'Nominal Melebihi Modal Investor/Saldo !!');
        }

        $store = $db->withdraw($data);


        return redirect()->route('billing')->with($store['status'], $store['message']);
    }

    public function keluar_all()
    {
        $investor = InvestorModal::all();

        return view('billing.form-deposit.keluar-all', [
            'investor' => $investor,
        ]);
    }

    public function keluar_all_store(Request $request)
    {
        $data = $request->validate([
            'nominal' => 'required',
        ]);

        $db = new KasBesar();
        $saldo = $db->saldoTerakhir();

        $data['nominal'] = str_replace('.', '', $data['nominal']);

        if($saldo < $data['nominal']){
            return redirect()->back()->with('error', 'Saldo Kas Besar Tidak Mencukupi !!');
        }

        $store = $db->withdrawAll($data);

        return redirect()->route('billing')->with($store['status'], $store['message']);
    }
}
