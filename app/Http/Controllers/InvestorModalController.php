<?php

namespace App\Http\Controllers;

use App\Models\InvestorModal;
use Illuminate\Http\Request;

class InvestorModalController extends Controller
{
    public function index()
    {
        $data = InvestorModal::all();
        return view('db.investor-modal.index', [
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required',
            'no_hp' => 'required',
            'no_rek' => 'required',
            'bank' => 'required',
            'nama_rek' => 'required',
        ]);

        $store = InvestorModal::create($data);

        return redirect()->route('db.investor-modal')->with('success', 'Data berhasil ditambahkan');
    }

    public function update(InvestorModal $investor, Request $request)
    {
        $data = $request->validate([
            'nama' => 'required',
            'no_hp' => 'required',
            'no_rek' => 'required',
            'bank' => 'required',
            'nama_rek' => 'required',
        ]);

        $investor->update($data);

        return redirect()->route('db.investor-modal')->with('success', 'Data berhasil diubah');
    }

    public function destroy(InvestorModal $investor)
    {
        $investor->delete();

        return redirect()->route('db.investor-modal')->with('success', 'Data berhasil dihapus');
    }
}
