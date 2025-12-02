<?php

namespace App\Http\Controllers;

use App\Models\db\Barang\BarangKategori;
use App\Models\db\Barang\BarangNama;
use App\Models\db\Barang\BarangStokHarga;
use App\Models\db\Barang\BarangType;
use App\Models\db\Barang\BarangUnit;
use App\Models\db\Karyawan;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function stok_all(Request $request)
    {
         $kategori = BarangKategori::with(['barang_nama'])->get();
        $type = BarangType::with(['unit', 'barangs'])->get();

        $unitFilter = $request->input('unit');
        $typeFilter = $request->input('type');
        $kategoriFilter = $request->input('kategori');
        $barangNamaFilter = $request->input('barang_nama');

        if (! empty($unitFilter) && $unitFilter != '') {
            $selectType = BarangType::whereIn('barang_unit_id', $unitFilter)->get();

            $selectKategori = BarangKategori::whereHas('barangs', function ($query) use ($unitFilter) {
                $query->whereHas('type', function ($query) use ($unitFilter) {
                    $query->whereIn('barang_unit_id', $unitFilter);
                });
            })->get();

            $selectBarangNama = BarangNama::whereHas('barang', function ($query) use ($unitFilter) {
                $query->whereHas('type', function ($query) use ($unitFilter) {
                    $query->whereIn('barang_unit_id', $unitFilter);
                });
            })->get();

        } else {
            $selectType = BarangType::all();
            $selectKategori = BarangKategori::all();
            $selectBarangNama = BarangNama::select('id', 'nama')->distinct()->orderBy('id')->get();
        }

        $db = new BarangStokHarga();

        $data = $db->barangStokAll($unitFilter, $typeFilter, $kategoriFilter, $barangNamaFilter);
        $units = BarangUnit::all();
        $karyawan = Karyawan::where('status', 1)->get();

        return view('user.stok-all.index', [
            'data' => $data,
            'kategori' => $kategori,
            'units' => $units,
            'type' => $type,
            'unitFilter' => $unitFilter,
            'typeFilter' => $typeFilter,
            'kategoriFilter' => $kategoriFilter,
            'selectType' => $selectType,
            'selectKategori' => $selectKategori,
            'barangNamaFilter' => $barangNamaFilter,
            'selectBarangNama' => $selectBarangNama,
            'karyawan' => $karyawan,
        ]);
    }
}
