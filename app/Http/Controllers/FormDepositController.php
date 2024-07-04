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
        $investor = InvestorModal::all();

        return view('billing.form-deposit.masuk', [
            'investor' => $investor,
        ]);
    }

    public function masuk_store(Request $request)
    {
        $data = $request->validate([
            'nominal' => 'required',
            'investor_modal_id' => 'required|exists:investor_modals,id',
            'ppn_kas' => 'required'
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
            'ppn_kas' => 'required', // Add this line
            'investor_modal_id' => 'required|exists:investor_modals,id',
        ]);

        $db = new KasBesar();
        $modal = $db->modalInvestorTerakhir($data['ppn_kas']) * -1;
        $saldo = $db->saldoTerakhir($data['ppn_kas']);

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
            'ppn_kas' => 'required', // Add this line
        ]);

        $db = new KasBesar();
        $saldo = $db->saldoTerakhir($data['ppn_kas']);

        $data['nominal'] = str_replace('.', '', $data['nominal']);

        if($saldo < $data['nominal']){
            return redirect()->back()->with('error', 'Saldo Kas Besar Tidak Mencukupi !!');
        }

        $store = $db->withdrawAll($data);

        return redirect()->route('billing')->with($store['status'], $store['message']);
    }

    public function getRekening(Request $request)
    {
        $kas = $request->ppn_kas == 1 ? 'kas-besar-ppn' : 'kas-besar-non-ppn';

        $rekening = Rekening::where('untuk', $kas)->first();

        if(!$rekening){
            return response()->json([
                'status' => 0,
                'message' => 'Rekening Tidak Ditemukan !!'
            ]);
        }

        return response()->json([
            'status' => 1,
            'data' => $rekening]);

    }
}
