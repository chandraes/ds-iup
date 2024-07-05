<?php

namespace App\Http\Controllers;

use App\Models\db\Barang\BarangType;
use App\Models\db\Barang\BarangUnit;
use Illuminate\Http\Request;

class BarangController extends Controller
{
    public function unit()
    {
        $data = BarangUnit::with(['types'])->get();

        return view('db.unit.index', [
            'data' => $data
        ]);
    }

    public function unit_store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required',
        ]);

        BarangUnit::create($data);

        return redirect()->back()->with('success', 'Data berhasil ditambahkan');
    }

    public function unit_update(Request $request, BarangUnit $unit)
    {
        $data = $request->validate([
            'nama' => 'required',
        ]);

        $unit->update($data);

        return redirect()->back()->with('success', 'Data berhasil diubah');
    }

    public function unit_delete(BarangUnit $unit)
    {
        if($unit->types->count() > 0) return redirect()->back()->with('error', 'Data tidak bisa dihapus karena masih memiliki type terkait');

        $unit->delete();

        return redirect()->back()->with('success', 'Data berhasil dihapus');
    }

    public function type_store(Request $request)
    {
        $data = $request->validate([
            'barang_unit_id' => 'required',
            'nama' => 'required',
        ]);

        BarangType::create($data);

        return redirect()->back()->with('success', 'Data berhasil ditambahkan');
    }

    public function type_update(Request $request, BarangType $type)
    {
        $data = $request->validate([
            'barang_unit_id' => 'required',
            'nama' => 'required',
        ]);

        $type->update($data);

        return redirect()->back()->with('success', 'Data berhasil diubah');
    }

    public function type_delete(BarangType $type)
    {
        $type->delete();

        return redirect()->back()->with('success', 'Data berhasil dihapus');
    }
}
