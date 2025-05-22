<?php

namespace App\Http\Controllers;

use App\Models\db\Barang\BarangKategori;
use App\Models\db\Barang\BarangNama;
use App\Models\db\Barang\BarangStokHarga;
use App\Models\db\Barang\BarangType;
use App\Models\db\Barang\BarangUnit;
use App\Models\db\Karyawan;
use App\Models\db\KodeToko;
use App\Models\db\Konsumen;
use App\Models\db\Pajak;
use App\Models\Wilayah;
use Illuminate\Http\Request;

class PerusahaanController extends Controller
{
    public function konsumen(Request $request)
    {
        $filters = $request->only(['area', 'kab_kota','kecamatan', 'kode_toko', 'status']); // Ambil filter dari request

        $kecamatan_filter = Wilayah::whereIn('id_induk_wilayah', function ($query) {
            $query->select('id_wilayah')
                ->from('wilayahs')
                ->where('id_induk_wilayah', '110000');
        })->where('id_level_wilayah', 3)->get();

        $provinsi = Wilayah::where('id_level_wilayah', 1)->get();

        $sales_area = Karyawan::with('jabatan')->whereHas('jabatan', function ($query) {
            $query->where('is_sales', 1);
        })->select('id', 'nama')->get();

        return view('perusahaan.konsumen', [
            'provinsi' => $provinsi,
            'sales_area' => $sales_area,
            'kode_toko' => KodeToko::select('id', 'kode')->get(),
            'kecamatan_filter' => $kecamatan_filter,
        ]);
    }

    public function konsumen_data(Request $request)
    {
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $search = $request->input('search.value');
        $filters = $request->only(['area', 'kecamatan', 'kode_toko', 'status']);

        $query = Konsumen::with(['provinsi', 'kabupaten_kota', 'kecamatan', 'sales_area', 'kode_toko', 'karyawan'])
            ->filter($filters);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%$search%")
                ->orWhere('no_hp', 'like', "%$search%");
            });
        }

        $total = $query->count();
        $data = $query->skip($start)->take($length)->get();

        // Format data sesuai kebutuhan DataTables
        $result = [];
        foreach ($data as $d) {
            $result[] = [
                $d->full_kode,
                $d->kode_toko ? $d->kode_toko->kode : '',
                $d->nama,
                $d->karyawan ? $d->karyawan->nama : '',
                $d->provinsi ? $d->provinsi->nama_wilayah : '',
                $d->kabupaten_kota ? $d->kabupaten_kota->nama_wilayah : '',
                $d->kecamatan ? $d->kecamatan->nama_wilayah : '',
                $d->alamat,
            ];
        }

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $result,
        ]);
    }

    public function sales(Request $request)
    {
         $data = Karyawan::with('jabatan')->whereHas('jabatan', function ($query) {
                    $query->where('is_sales', 1);
                })->get();

        return view('perusahaan.sales', [
            'data' => $data,
        ]);
    }

    public function stok_ppn(Request $request)
    {
         // $kategori = BarangKategori::with(['barang_nama'])->get();
        // $type = BarangType::with(['unit', 'barangs'])->get();
        $ppnRate = Pajak::where('untuk', 'ppn')->first()->persen;

        $unitFilter = auth()->user()->barang_unit_id;
        $typeFilter = $request->input('type');
        $kategoriFilter = $request->input('kategori');
        $barangNamaFilter = $request->input('barang_nama');

        if (! empty($unitFilter) && $unitFilter != '') {
            $selectType = BarangType::where('barang_unit_id', $unitFilter)->get();

            $selectKategori = BarangKategori::whereHas('barangs', function ($query) use ($unitFilter) {
                $query->whereHas('type', function ($query) use ($unitFilter) {
                    $query->where('barang_unit_id', $unitFilter);
                });
            })->get();

            $selectBarangNama = BarangNama::whereHas('barang', function ($query) use ($unitFilter) {
                $query->whereHas('type', function ($query) use ($unitFilter) {
                    $query->where('barang_unit_id', $unitFilter);
                });
            })->get();

        } else {
            $selectType = BarangType::all();
            $selectKategori = BarangKategori::all();
            $selectBarangNama = BarangNama::select('id', 'nama')->distinct()->orderBy('id')->get();
        }

        $db = new BarangStokHarga();

        $jenis = 1;

        $data = $db->barangStokV3($jenis, $unitFilter, $typeFilter, $kategoriFilter, $barangNamaFilter);
        $karyawan = Karyawan::where('status', 1)->get();

        return view('perusahaan.stok-ppn', [
            'data' => $data,
            // 'kategori' => $kategori,
            // 'units' => $units,
            // 'type' => $type,
            'unitFilter' => $unitFilter,
            'typeFilter' => $typeFilter,
            'kategoriFilter' => $kategoriFilter,
            'selectType' => $selectType,
            'selectKategori' => $selectKategori,
            'ppnRate' => $ppnRate,
            'barangNamaFilter' => $barangNamaFilter,
            'selectBarangNama' => $selectBarangNama,
            'karyawan' => $karyawan,
        ]);
    }

    public function stok_non_ppn(Request $request)
    {
        $unitFilter = auth()->user()->barang_unit_id;
        $typeFilter = $request->input('type');
        $kategoriFilter = $request->input('kategori');
        $barangNamaFilter = $request->input('barang_nama');



        if (! empty($unitFilter) && $unitFilter != '') {
            $selectType = BarangType::where('barang_unit_id', $unitFilter)->get();

            $selectKategori = BarangKategori::whereHas('barangs', function ($query) use ($unitFilter) {
                $query->whereHas('type', function ($query) use ($unitFilter) {
                    $query->where('barang_unit_id', $unitFilter);
                });
            })->get();

            $selectBarangNama = BarangNama::whereHas('barang', function ($query) use ($unitFilter) {
                $query->whereHas('type', function ($query) use ($unitFilter) {
                    $query->where('barang_unit_id', $unitFilter);
                });
            })->get();

        } else {
            $selectType = BarangType::all();
            $selectKategori = BarangKategori::all();
            $selectBarangNama = BarangNama::select('id', 'nama')->distinct()->orderBy('id')->get();
        }

        $db = new BarangStokHarga;

        $jenis = 2;

        $data = $db->barangStokV3($jenis, $unitFilter, $typeFilter, $kategoriFilter, $barangNamaFilter);
        $units = BarangUnit::all();
        $karyawan = Karyawan::where('status', 1)->get();

        return view('perusahaan.stok-non-ppn', [
            'data' => $data,
            // 'kategori' => $kategori,
            // 'type' => $type,
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
