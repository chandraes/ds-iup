<?php

namespace App\Http\Controllers;

use App\Models\db\Barang\Barang;
use App\Models\db\Barang\BarangKategori;
use App\Models\db\Barang\BarangNama;
use App\Models\db\Barang\BarangStokHarga;
use App\Models\db\Barang\BarangType;
use App\Models\db\Barang\BarangUnit;
use App\Models\db\Pajak;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BarangController extends Controller
{

    private function storeDb($model, $data)
    {
        try {
            DB::beginTransaction();
            $model::create($data);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi masalah saat menambahkan data. '.$th->getMessage());
        }

        return redirect()->back()->with('success', 'Data berhasil ditambahkan');
    }

    public function barang_kategori()
    {
        $data = BarangKategori::with(['barang_nama'])->withCount('barang_nama')->get();

        return view('db.kategori-barang.index', [
            'data' => $data
        ]);
    }

    public function barang_nama_store(Request $request)
    {
        $data = $request->validate([
            'barang_kategori_id' => 'required|exists:barang_kategoris,id',
            'nama' => 'required',
        ]);

        $this->storeDb(BarangNama::class, $data);

        return redirect()->back()->with('success', 'Data berhasil ditambahkan');

    }

    public function barang_nama_update(Request $request, BarangNama $nama)
    {
        $data = $request->validate([
            'barang_kategori_id' => 'required|exists:barang_kategoris,id',
            'nama' => 'required',
        ]);

        $nama->update($data);

        return redirect()->back()->with('success', 'Data berhasil diubah');
    }

    public function barang_nama_delete(BarangNama $nama)
    {
        if($nama->barang->count() > 0) return redirect()->back()->with('error', 'Data tidak bisa dihapus karena masih memiliki barang terkait');

        $nama->delete();

        return redirect()->back()->with('success', 'Data berhasil dihapus');
    }

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

        $this->storeDb(BarangUnit::class, $data);

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

        try {
            DB::beginTransaction();
            BarangType::create($data);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menambahkan data. '.$th->getMessage());
        }

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

    public function barang(Request $request)
    {
        $kategoriDb = new BarangKategori();
        // $data = BarangType::with(['unit', 'barangs'])->get();

        $unitFilter = $request->input('unit');
        $typeFilter = $request->input('type');
        $kategoriFilter = $request->input('kategori');
        $jenisFilter = $request->input('jenis');

        if (!empty($unitFilter) && $unitFilter != '') {
            $selectType = BarangType::where('barang_unit_id', $unitFilter)->get();

            $selectKategori = $kategoriDb->whereHas('barangs', function ($query) use ($unitFilter) {
                $query->whereHas('type', function ($query) use ($unitFilter) {
                    $query->where('barang_unit_id', $unitFilter);
                });
            })->get();

        } else {
            $selectType = BarangType::all();
            $selectKategori = $kategoriDb->get();
        }

        $db = new BarangUnit();

        $units = $db->barangAll($unitFilter, $typeFilter, $kategoriFilter, $jenisFilter);
        $kategori = $kategoriDb->with('barang_nama')->get();

        return view('db.barang.index', [
            // 'data' => $data,
            'kategori' => $kategori,
            'units' => $units,
            'unitFilter' => $unitFilter,
            'typeFilter' => $typeFilter,
            'kategoriFilter' => $kategoriFilter,
            'jenisFilter' => $jenisFilter,
            'selectType' => $selectType,
            'selectKategori' => $selectKategori,
        ]);
    }

    public function barang_store(Request $request)
    {
        $data = $request->validate([
            'barang_type_id' => 'required|exists:barang_types,id',
            'barang_kategori_id' => 'required|exists:barang_kategoris,id',
            'barang_nama_id' => 'required|exists:barang_namas,id',
            'jenis' => 'required|in:1,2,3',
            'kode' => 'nullable',
            'merk' => 'required',
        ]);

        $this->storeDb(Barang::class, $data);

        return redirect()->back()->with('success', 'Berhasil menambahkan data barang!');


    }

    public function barang_update(Request $request, Barang $barang)
    {
        $data = $request->validate([
            'barang_type_id' => 'required|exists:barang_types,id',
            'barang_kategori_id' => 'required|exists:barang_kategoris,id',
            'barang_nama_id' => 'required|exists:barang_namas,id',
            'jenis' => 'required|in:1,2,3',
            'kode' => 'nullable',
            'merk' => 'required',
        ]);

        $barang->update($data);

        return redirect()->back()->with('success', 'Berhasil mengubah data barang!');
    }

    public function barang_delete(Barang $barang)
    {
        $errorMessage = null;
        if ($barang->stok_harga && $barang->stok_harga->sum('stok') > 0) {
            $errorMessage = 'Data tidak bisa dihapus karena masih memiliki stok!';
        }

        if ($errorMessage) {
            return redirect()->back()->with('error', $errorMessage);
        }

        $barang->delete();
        return redirect()->back()->with('success', 'Berhasil menghapus data barang!');
    }

    public function kategori_barang_store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required',
        ]);

        BarangKategori::create($data);

        return redirect()->back()->with('success', 'Data berhasil ditambahkan');
    }

    public function kategori_barang_update(Request $request, BarangKategori $kategori)
    {
        $data = $request->validate([
            'nama' => 'required',
        ]);

        $kategori->update($data);

        return redirect()->back()->with('success', 'Data berhasil diubah');
    }

    public function kategori_barang_delete(BarangKategori $kategori)
    {
        if($kategori->barangs->count() > 0) return redirect()->back()->with('error', 'Data tidak bisa dihapus karena masih memiliki barang terkait');

        $kategori->delete();

        return redirect()->back()->with('success', 'Data berhasil dihapus');
    }

    public function get_type(Request $request)
    {
        $data = BarangType::where('barang_unit_id', $request->unit_id)->get();

        if ($data->isEmpty()) {
            return response()->json([
                'status' => 0,
                'message' => 'Unit belum memiliki type!!'
            ]);
        }

        return response()->json([
            'status' => 1,
            'data' => $data
        ]);
    }

    public function get_barang_nama(Request $request)
    {
        $data = BarangNama::where('barang_kategori_id', $request->kategori_id)->get();

        if ($data->isEmpty()) {
            return response()->json([
                'status' => 0,
                'message' => 'Kategori belum memiliki nama barang!!'
            ]);
        }

        return response()->json([
            'status' => 1,
            'data' => $data
        ]);
    }

    public function stok_ppn(Request $request)
    {
        $kategori = BarangKategori::with(['barang_nama'])->get();
        $data = BarangType::with(['unit', 'barangs'])->get();
        $ppnRate = Pajak::where('untuk', 'ppn')->first()->persen;

        $unitFilter = $request->input('unit');
        $typeFilter = $request->input('type');
        $kategoriFilter = $request->input('kategori');

        if (!empty($unitFilter) && $unitFilter != '') {
            $selectType = BarangType::where('barang_unit_id', $unitFilter)->get();

            $selectKategori = BarangKategori::whereHas('barangs', function ($query) use ($unitFilter) {
                $query->whereHas('type', function ($query) use ($unitFilter) {
                    $query->where('barang_unit_id', $unitFilter);
                });
            })->get();

        } else {
            $selectType = BarangType::all();
            $selectKategori = BarangKategori::all();
        }

        $db = new BarangUnit();

        $jenis = 1;

        $units = $db->barangStok($jenis, $unitFilter, $typeFilter, $kategoriFilter);

        // $unitsQuery = BarangUnit::with([
        //     'types' => function ($query) use ($typeFilter, $kategoriFilter) {
        //         if ($typeFilter) {
        //             $query->where('id', $typeFilter);
        //         }
        //         $query->with(['barangs' => function ($query) use ($kategoriFilter) {
        //             if ($kategoriFilter) {
        //                 $query->where('barang_kategori_id', $kategoriFilter);
        //             }
        //             $query->with(['kategori', 'barang_nama', 'stok_harga' => function($q) {
        //                 $q->where('stok', '>', 0);
        //             }])->whereIn('jenis', [1,3]); // Eager load kategori and nama for each barang
        //         }])
        //         ->withCount('barangs as totalBarangs'); // Count barangs directly in the query
        //     },
        // ]);

        // if ($unitFilter) {
        //     $unitsQuery->where('id', $unitFilter);
        // }

        // $units = $unitsQuery->get();

        // $units->loadMissing('types.barangs.kategori', 'types.barangs.barang_nama', 'types.barangs.stok_harga');

        // foreach ($units as $unit) {
        //     $unit->unitRowspan = 0;

        //     foreach ($unit->types as $type) {
        //         $groupedBarangs = $type->barangs->groupBy('kategori.nama');
        //         $type->groupedBarangs = $groupedBarangs;
        //         $type->typeRowspan = 0;

        //         foreach ($groupedBarangs as $kategoriNama => $barangs) {
        //             $groupedByNama = $barangs->groupBy('barang_nama.nama');

        //             foreach ($groupedByNama as $nama => $namaBarangs) {
        //                 foreach ($namaBarangs as $barang) {
        //                     $barang->kategoriRowspan = 0;
        //                     $barang->namaRowspan = 0;
        //                     $barang->stokPpnRowspan = $barang->stok_harga->count();

        //                     $barang->kategoriRowspan += $barang->stokPpnRowspan;
        //                     $barang->namaRowspan += $barang->stokPpnRowspan;
        //                 }

        //                 $type->typeRowspan += $barang->namaRowspan;
        //                 $unit->unitRowspan += $barang->namaRowspan;
        //             }
        //         }
        //     }
        // }

        // dd($units->toArray());
        return view('db.stok-ppn.index', [
            'data' => $data,
            'kategori' => $kategori,
            'units' => $units,
            'unitFilter' => $unitFilter,
            'typeFilter' => $typeFilter,
            'kategoriFilter' => $kategoriFilter,
            'selectType' => $selectType,
            'selectKategori' => $selectKategori,
            'ppnRate' => $ppnRate,
        ]);
    }

    public function stok_harga_update(Request $request, BarangStokHarga $barang)
    {
        $data = $request->validate([
            'harga' => 'required',
        ]);

        $data['harga'] = str_replace('.', '', $data['harga']);

        if ($data['harga'] < $barang->harga_beli) {
            return redirect()->back()->with('error', 'Harga jual tidak boleh lebih kecil dari harga beli!');
        }

        $barang->update($data);

        return redirect()->back()->with('success', 'Berhasil mengubah data harga!');
    }

    public function stok_non_ppn(Request $request)
    {
        $kategori = BarangKategori::with(['barang_nama'])->get();
        $data = BarangType::with(['unit', 'barangs'])->get();
        $ppnRate = Pajak::where('untuk', 'ppn')->first()->persen;

        $unitFilter = $request->input('unit');
        $typeFilter = $request->input('type');
        $kategoriFilter = $request->input('kategori');

        if (!empty($unitFilter) && $unitFilter != '') {
            $selectType = BarangType::where('barang_unit_id', $unitFilter)->get();

            $selectKategori = BarangKategori::whereHas('barangs', function ($query) use ($unitFilter) {
                $query->whereHas('type', function ($query) use ($unitFilter) {
                    $query->where('barang_unit_id', $unitFilter);
                });
            })->get();

        } else {
            $selectType = BarangType::all();
            $selectKategori = BarangKategori::all();
        }

        $db = new BarangUnit();

        $jenis = 2;

        $units = $db->barangStok($jenis, $unitFilter, $typeFilter, $kategoriFilter);

        // $unitsQuery = BarangUnit::with([
        //     'types' => function ($query) use ($typeFilter, $kategoriFilter) {
        //         if ($typeFilter) {
        //             $query->where('id', $typeFilter);
        //         }
        //         $query->with(['barangs' => function ($query) use ($kategoriFilter) {
        //             if ($kategoriFilter) {
        //                 $query->where('barang_kategori_id', $kategoriFilter);
        //             }
        //             $query->with(['kategori', 'barang_nama', 'stok_harga' => function($q) {
        //                 $q->where('stok', '>', 0);
        //             }])->whereIn('jenis', [2,3]); // Eager load kategori and nama for each barang
        //         }])
        //         ->withCount('barangs as totalBarangs'); // Count barangs directly in the query
        //     },
        // ]);

        // if ($unitFilter) {
        //     $unitsQuery->where('id', $unitFilter);
        // }

        // $units = $unitsQuery->get();

        // $units->loadMissing('types.barangs.kategori', 'types.barangs.barang_nama', 'types.barangs.stok_harga');

        // foreach ($units as $unit) {
        //     $unit->unitRowspan = 0;

        //     foreach ($unit->types as $type) {
        //         $groupedBarangs = $type->barangs->groupBy('kategori.nama');
        //         $type->groupedBarangs = $groupedBarangs;
        //         $type->typeRowspan = 0;

        //         foreach ($groupedBarangs as $kategoriNama => $barangs) {
        //             $groupedByNama = $barangs->groupBy('barang_nama.nama');

        //             foreach ($groupedByNama as $nama => $namaBarangs) {
        //                 foreach ($namaBarangs as $barang) {
        //                     $barang->kategoriRowspan = 0;
        //                     $barang->namaRowspan = 0;
        //                     $barang->stokPpnRowspan = $barang->stok_harga->count();

        //                     $barang->kategoriRowspan += $barang->stokPpnRowspan;
        //                     $barang->namaRowspan += $barang->stokPpnRowspan;
        //                 }

        //                 $type->typeRowspan += $barang->namaRowspan;
        //                 $unit->unitRowspan += $barang->namaRowspan;
        //             }
        //         }
        //     }
        // }

        // dd($units->toArray());
        return view('db.stok-non-ppn.index', [
            'data' => $data,
            'kategori' => $kategori,
            'units' => $units,
            'unitFilter' => $unitFilter,
            'typeFilter' => $typeFilter,
            'kategoriFilter' => $kategoriFilter,
            'selectType' => $selectType,
            'selectKategori' => $selectKategori,

        ]);
    }


}
