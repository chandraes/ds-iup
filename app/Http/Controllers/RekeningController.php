<?php

namespace App\Http\Controllers;

use App\Models\Rekening;
use Illuminate\Http\Request;

class RekeningController extends Controller
{
    public function index()
    {
        $data = Rekening::all();
        return view('db.rekening.index', [
            'data' => $data
        ]);
    }

    public function update(Request $request, Rekening $rekening)
    {
        $data = $request->validate([
            'bank' => 'required',
            'no_rek' => 'required',
            'nama_rek' => 'required',
        ]);

        $rekening->update($data);

        return redirect()->back()->with('success', 'Data berhasil diubah!');
    }
}
