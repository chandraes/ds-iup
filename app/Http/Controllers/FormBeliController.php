<?php

namespace App\Http\Controllers;

use App\Models\db\Barang\BarangUnit;
use Illuminate\Http\Request;

class FormBeliController extends Controller
{
    public function index(Request $request)
    {
        $req = $request->validate([
            'kas_ppn' => 'required',
            'stok' => 'required',
        ]);

        $data = BarangUnit::with(['types'])->get();

        return view('billing.form-beli.index', [
            'data' => $data,
            'req' => $req
        ]);
    }
}
