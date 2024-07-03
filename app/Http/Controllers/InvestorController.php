<?php

namespace App\Http\Controllers;

use App\Models\Investor;
use Illuminate\Http\Request;

class InvestorController extends Controller
{
    public function index()
    {
        $data = Investor::all();
        return view('db.investor.index', [
            'data' => $data
        ]);
    }

    public function update(Request $request, Investor $investor)
    {
        $data = $request->validate([
            'persentase' => 'required|integer',
        ]);

        $check = Investor::whereNot('id', $investor->id)->sum('persentase') + $data['persentase'];

        if ($check > 100) {
            return redirect()->back()->with('error', 'Persentase investor melebihi 100%');
        }

        $investor->update($data);

        return redirect()->back()->with('success', 'Data berhasil diubah!');
    }

}
