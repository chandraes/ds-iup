<?php

namespace App\Http\Controllers;

use App\Models\db\Barang\Barang;
use App\Models\db\Barang\BarangGrosir;
use App\Models\db\Barang\BarangKategori;
use App\Models\db\Barang\BarangNama;
use App\Models\db\Barang\BarangStokHarga;
use App\Models\db\Barang\BarangType;
use App\Models\db\Barang\BarangUnit;
use App\Models\db\Barang\Subpg;
use App\Models\db\Karyawan;
use App\Models\db\Pajak;
use App\Models\db\Satuan;
use App\Models\GantiRugi;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

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

    public function barang_kategori(Request $request)
    {
        $data = BarangKategori::with(['barang_nama' => function ($query) use ($request) {
            if ($request->input('filter_barang_nama')) {
                $query->where('id', $request->input('filter_barang_nama'));
            }
        }])
            ->withCount(['barang_nama' => function ($query) use ($request) {
                if ($request->input('filter_barang_nama')) {
                    $query->where('id', $request->input('filter_barang_nama'));
                }
            }])
            ->orderBy('urut')->get();

        $barangNama = BarangNama::select('id', 'nama')->get();

        return view('db.kategori-barang.index', [
            'data' => $data,
            'barangNama' => $barangNama,
        ]);
    }

    public function barang_nama_store(Request $request)
    {
        $data = $request->validate([
            'barang_kategori_id' => 'required|exists:barang_kategoris,id',
            'nama' => [
                'required',
                Rule::unique('barang_namas')->where(function ($query) use ($request) {
                    return $query->where('barang_kategori_id', $request->barang_kategori_id)
                        ->where('nama', $request->nama);
                }),
            ],
        ]);

        $this->storeDb(BarangNama::class, $data);

        return redirect()->back()->with('success', 'Data berhasil ditambahkan');

    }

    public function barang_nama_update(Request $request, BarangNama $nama)
    {
        $data = $request->validate([
            'barang_kategori_id' => 'required|exists:barang_kategoris,id',
            'nama' => [
                'required',
                Rule::unique('barang_namas')->where(function ($query) use ($request) {
                    return $query->where('barang_kategori_id', $request->barang_kategori_id);
                })->ignore($nama->id, 'id'),
            ],
        ]);

        $nama->update($data);

        return redirect()->back()->with('success', 'Data berhasil diubah');
    }

    public function barang_nama_delete(BarangNama $nama)
    {
        if ($nama->barang->count() > 0) {
            return redirect()->back()->with('error', 'Data tidak bisa dihapus karena masih memiliki barang terkait');
        }

        $nama->delete();

        return redirect()->back()->with('success', 'Data berhasil dihapus');
    }

    public function unit()
    {
        $data = BarangUnit::with(['types'])->get();

        return view('db.unit.index', [
            'data' => $data,
        ]);
    }

    public function unit_store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|unique:barang_units,nama',
        ]);

        $this->storeDb(BarangUnit::class, $data);

        return redirect()->back()->with('success', 'Data berhasil ditambahkan');
    }

    public function unit_update(Request $request, BarangUnit $unit)
    {
        $data = $request->validate([
            'nama' => 'required|unique:barang_units,nama,'.$unit->id.',id',
        ]);

        $unit->update($data);

        return redirect()->back()->with('success', 'Data berhasil diubah');
    }

    public function unit_delete(BarangUnit $unit)
    {
        if ($unit->types->count() > 0) {
            return redirect()->back()->with('error', 'Data tidak bisa dihapus karena masih memiliki type terkait');
        }

        $unit->delete();

        return redirect()->back()->with('success', 'Data berhasil dihapus');
    }

    public function type_store(Request $request)
    {
        $data = $request->validate([
            'barang_unit_id' => 'required|exists:barang_units,id',
            'nama' => [
                'required',
                Rule::unique('barang_types')->where(function ($query) use ($request) {
                    return $query->where('barang_unit_id', $request->barang_unit_id)
                        ->where('nama', $request->nama);
                }),
            ],
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
            'barang_unit_id' => 'required|exists:barang_units,id',
            'nama' => [
                'required',
                Rule::unique('barang_types')->where(function ($query) use ($request) {
                    return $query->where('barang_unit_id', $request->barang_unit_id);
                })->ignore($type->id, 'id'),
            ],
        ]);

        $type->update($data);

        return redirect()->back()->with('success', 'Data berhasil diubah');
    }

    public function type_delete(BarangType $type)
    {
        if ($type->barangs->count() > 0) {
            return redirect()->back()->with('error', 'Data tidak bisa dihapus karena masih memiliki barang terkait');
        }

        $type->delete();

        return redirect()->back()->with('success', 'Data berhasil dihapus');
    }

    public function barang_get_grosir(Request $request)
    {
        $data = $request->validate([
            'barang_id' => 'required|exists:barangs,id',
        ]);

        $grosir = BarangGrosir::with(['barang.satuan','satuan'])->where('barang_id',$data['barang_id'])->get();

        return response()->json([
            'status' => 'success',
            'data' => $grosir,
        ]);
    }

    public function barang_grosir_delete(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|exists:barang_grosirs,id',
        ]);
        try {
            DB::beginTransaction();
            $grosir = BarangGrosir::findOrFail($data['id']);
            $barang = Barang::findOrFail($grosir->barang_id);

            $grosir->delete();

            // Cek apakah masih ada grosir lain untuk barang ini
            $hasGrosir = BarangGrosir::where('barang_id', $barang->id)->exists();
            
            if (! $hasGrosir) {
                // Jika tidak ada grosir lain, set is_grosir ke false
                $barang->is_grosir = false;
                $barang->save();
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menghapus data. '.$th->getMessage(),
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data grosir berhasil dihapus',
        ]);
    }

    public function barang_grosir_store(Request $request)
    {
    //    using ajax
        $data = $request->validate([
            'barang_id' => 'required|exists:barangs,id',
            'qty_grosir' => 'required|numeric|max:100',
            'satuan_id' => 'required|exists:satuans,id',
            'diskon' => 'required|numeric|min:0|max:100',
        ]);

        try {
            DB::beginTransaction();
            BarangGrosir::create($data);

            $barang = Barang::findOrFail($data['barang_id']);
            $barang->is_grosir = true;
            $barang->save();


            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menambahkan data. '.$th->getMessage(),
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data grosir berhasil ditambahkan',
        ]);


    }

    public function barang_data(Request $request)
    {
        $filters = $request->only(['barang_nama', 'kategori', 'jenis', 'type', 'unit']); // Ambil filter dari request

        $data = Barang::query()->with(['unit', 'type', 'kategori', 'barang_nama', 'satuan'])
                ->filter($filters)
                ->limit(10)
;

        return DataTables::of($data)
            // add unique barang_unit_id becouse its ambiguous
            ->addColumn('unit', function ($d) {
                // Use the relation if loaded, fallback to direct attribute if not
                return $d->unit ? $d->unit->nama : $d->barang_unit_id;
            })
            ->addColumn('upload_barang', function ($d) {
                return view('db.barang._upload-foto', compact('d'))->render();
            })
            ->addColumn('diskon_view', function ($d){
                return view('db.barang._diskon', compact('d'))->render();
            })
            ->addColumn('grosir_view', function ($d){
                return view('db.barang._grosir', compact('d'))->render();
            })
            ->addColumn('aksi', function ($d) {
                return view('db.barang._aksi', compact('d'))->render();
            })
            ->addColumn('satuan_view', function ($d) {
                return view('db.barang._satuan', compact('d'))->render();
            })
            ->rawColumns(['foto', 'diskon_view','aksi', 'upload_barang', 'satuan_view', 'grosir_view'])
            ->make(true);
    }

    public function barang(Request $request)
    {
        $kategoriDb = new BarangKategori;

        $db = new BarangUnit;
        $units = $db->get();

        $kategori = $kategoriDb->with('barang_nama')->get();
        $subpg = Subpg::select('id', 'nama')->get();
        $satuan = Satuan::all();

        // dd($data);

        return view('db.barang.index', [
            'subpg' => $subpg,
            'kategori' => $kategori,
            'units' => $units,
            'satuan' => $satuan,
        ]);
    }

    public function barang_backup(Request $request)
    {
        $kategoriDb = new BarangKategori;
        $data = BarangType::with(['unit', 'barangs'])->get();

        $unitFilter = $request->input('unit');
        $typeFilter = $request->input('type');
        $kategoriFilter = $request->input('kategori');
        $jenisFilter = $request->input('jenis');
        $barangNamaFilter = $request->input('barang_nama');

        if (! empty($unitFilter) && $unitFilter != '') {
            $selectType = BarangType::where('barang_unit_id', $unitFilter)->get();

            $selectKategori = $kategoriDb->whereHas('barangs', function ($query) use ($unitFilter) {
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
            $selectType = BarangType::select('id', 'nama')->get();
            $selectKategori = $kategoriDb->get();
            $selectBarangNama = BarangNama::select('nama')->distinct()->orderBy('nama')->get();
        }

        $db = new BarangUnit;

        $units = $db->barangAll($unitFilter, $typeFilter, $kategoriFilter, $jenisFilter, $barangNamaFilter);
        $kategori = $kategoriDb->with('barang_nama')->get();
        $subpg = Subpg::select('id', 'nama')->get();
        $satuan = Satuan::all();

        // dd($data);

        return view('db.barang.index', [
            'data' => $data,
            'subpg' => $subpg,
            'kategori' => $kategori,
            'units' => $units,
            'unitFilter' => $unitFilter,
            'typeFilter' => $typeFilter,
            'kategoriFilter' => $kategoriFilter,
            'jenisFilter' => $jenisFilter,
            'barangNamaFilter' => $barangNamaFilter,
            'selectType' => $selectType,
            'selectKategori' => $selectKategori,
            'selectBarangNama' => $selectBarangNama,
            'satuan' => $satuan,
        ]);
    }

    public function barang_new(Request $request)
    {
        $filter = $request->only(['unit', 'type', 'kategori', 'jenis', 'barang_nama', 'pagination']);

        $pagination = $request->input('pagination', 10);

        $db = new Barang();
        $kategoriDb = new BarangKategori;
        $unitFilter = $request->input('unit');
        $typeFilter = $request->input('type');
        $kategoriFilter = $request->input('kategori');
        $jenisFilter = $request->input('jenis');
        // $barangNamaFilter = $request->input('barang_nama');

        if (! empty($unitFilter) && $unitFilter != '') {
            $selectType = BarangType::where('barang_unit_id', $unitFilter)->get();

            $selectKategori = $kategoriDb->whereHas('barangs', function ($query) use ($unitFilter) {
                $query->whereHas('type', function ($query) use ($unitFilter) {
                    $query->where('barang_unit_id', $unitFilter);
                });
            })->get();

            // $selectBarangNama = BarangNama::whereHas('barang', function ($query) use ($unitFilter) {
            //     $query->whereHas('type', function ($query) use ($unitFilter) {
            //         $query->where('barang_unit_id', $unitFilter);
            //     });
            // })->get();

        } else {
            $selectType = BarangType::all();
            $selectKategori = $kategoriDb->get();
            // $selectBarangNama = BarangNama::select('nama')->distinct()->orderBy('nama')->get();
        }
        $units = BarangUnit::all();

        $data = $db->getBarang($filter);

        $kategori = $kategoriDb->with('barang_nama')->get();

        $satuan = Satuan::all();

        return view('db.barang.index', [
            'data' => $data,
            'units' => $units,
            'filter' => $filter,
            'kategori' => $kategori,
            'unitFilter' => $unitFilter,
            'typeFilter' => $typeFilter,
            'kategoriFilter' => $kategoriFilter,
            'jenisFilter' => $jenisFilter,
            // 'barangNamaFilter' => $barangNamaFilter,
            'selectType' => $selectType,
            'selectKategori' => $selectKategori,
            // 'selectBarangNama' => $selectBarangNama,
            'satuan' => $satuan,
            'pagination' => $pagination,
        ]);

    }

    public function barang_v2(Request $request)
    {
        $data = BarangUnit::with(['types', 'types.barangs', 'types.barangs.detail_types', 'types.barangs.detail_types.type'])->get();

        return view('db.barang.index', [
            'data' => $data,
        ]);
    }

    public function barang_store(Request $request)
    {
        $data = $request->validate([
            'barang_type_id' => 'required|exists:barang_types,id',
            'barang_kategori_id' => 'required|exists:barang_kategoris,id',
            'barang_nama_id' => 'required|exists:barang_namas,id',
            'satuan_id' => 'required|exists:satuans,id',
            'subpg_id' => 'nullable|exists:subpgs,id',
            'jenis' => 'required|in:1,2',
            'kode' => 'nullable',
            'merk' => [
                'required',
                Rule::unique('barangs')->where(function ($query) use ($request) {
                    return $query->where('barang_type_id', $request->barang_type_id)
                        ->where('barang_kategori_id', $request->barang_kategori_id)
                        ->where('barang_nama_id', $request->barang_nama_id)
                        ->where('satuan_id', $request->satuan_id)
                        ->where('jenis', $request->jenis)
                        ->where('merk', $request->merk)
                        ->where('kode', $request->kode);
                }),
            ],
            'keterangan' => 'nullable',
            'detail_type' => 'nullable|array',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:512',
        ]);

        try {

            DB::beginTransaction();
            $detailType = null;
            if (isset($data['detail_type'])) {
                $detailType = $data['detail_type'];
                unset($data['detail_type']);
            }
            $data['barang_unit_id'] = BarangType::find($data['barang_type_id'])->barang_unit_id;

            if ($request->hasFile('foto')) {

                $file = $request->file('foto');
                $filename = 'new'.'_'.time().'.'.$file->getClientOriginalExtension();

                if (!Storage::disk('public')->exists('barang')) {
                    Storage::disk('public')->makeDirectory('barang');
                }

                $path = $file->storeAs('barang', $filename, 'public');
                $data['foto'] = $path;
            }

            $store = Barang::create($data);

            if ($detailType != null) {
                foreach ($detailType as $key => $value) {
                    $store->detail_types()->create([
                        'barang_type_id' => $value,
                    ]);
                }
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Terjadi masalah saat menambahkan data. '.$th->getMessage());
        }

        return redirect()->back()->with('success', 'Berhasil menambahkan data barang!');

    }

    public function barang_update(Request $request, Barang $barang)
    {
        $data = $request->validate([
            'barang_type_id' => 'required|exists:barang_types,id',
            'barang_kategori_id' => 'required|exists:barang_kategoris,id',
            'barang_nama_id' => 'required|exists:barang_namas,id',
            'satuan_id' => 'required|exists:satuans,id',
            'subpg_id' => 'nullable|exists:subpgs,id',
            'jenis' => 'required|in:1,2',
            'kode' => 'nullable',
            'merk' => [
                'required',
                Rule::unique('barangs')->where(function ($query) use ($request) {
                    return $query->where('barang_type_id', $request->barang_type_id)
                        ->where('barang_kategori_id', $request->barang_kategori_id)
                        ->where('barang_nama_id', $request->barang_nama_id)
                        ->where('jenis', $request->jenis)
                        ->where('satuan_id', $request->satuan_id)
                        ->where('merk', $request->merk)
                        ->where('kode', $request->kode);
                })->ignore($barang->id, 'id'),
            ],
            'keterangan' => 'nullable',
            'detail_type' => 'nullable|array',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:512',
        ]);

        try {
            DB::beginTransaction();

            $detailType = null;

            if (isset($data['detail_type'])) {
                $detailType = $data['detail_type'];
                unset($data['detail_type']);
            }

            $data['barang_unit_id'] = BarangType::find($data['barang_type_id'])->barang_unit_id;

            if ($request->hasFile('foto')) {
                if ($barang->foto && Storage::disk('public')->exists($barang->foto)) {
                    Storage::disk('public')->delete($barang->foto);
                }

                $file = $request->file('foto');
                $filename = $barang->id.'_'.time().'.'.$file->getClientOriginalExtension();

                if (!Storage::disk('public')->exists('barang')) {
                    Storage::disk('public')->makeDirectory('barang');
                }

                $path = $file->storeAs('barang', $filename, 'public');
                $data['foto'] = $path;
            }

            $store = $barang->update($data);

            $barang->detail_types()->delete();

            if ($detailType != null) {
                foreach ($detailType as $key => $value) {
                    $barang->detail_types()->create([
                        'barang_type_id' => $value,
                    ]);
                }
            }

            BarangStokHarga::where('barang_id', $barang->id)->update([
                'barang_unit_id' => $data['barang_unit_id'],
                'barang_type_id' => $data['barang_type_id'],
                'barang_kategori_id' => $data['barang_kategori_id'],
                'barang_nama_id' => $data['barang_nama_id'],
            ]);

            DB::commit();

        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Terjadi masalah saat mengubah data. '.$th->getMessage());
        }

        return redirect()->back()->with('success', 'Berhasil mengubah data barang!');
    }

    public function barang_upload_foto(Request $request, Barang $barang)
    {
        $data = $request->validate([
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:512',
        ]);


        if ($barang->foto && Storage::disk('public')->exists($barang->foto)) {
            Storage::disk('public')->delete($barang->foto);
        }

        $file = $request->file('foto');
        $filename = $barang->id.'_'.time().'.'.$file->getClientOriginalExtension();

        if (!Storage::disk('public')->exists('barang')) {
            Storage::disk('public')->makeDirectory('barang');
        }

        $path = $file->storeAs('barang', $filename, 'public');
        $data['foto'] = $path;

        $barang->update($data);

        $previousUrl = $request->input('previous_url');
        if ($previousUrl) {
            return redirect()->to($previousUrl)->with('success', 'Berhasil mengubah foto barang!');
        }

        return redirect()->back()->with('success', 'Berhasil mengubah foto barang!');
    }

    public function barang_diskon(Request $request, Barang $barang)
    {
        $data = $request->validate([
            'diskon' => 'required|numeric|min:0|max:100',
            'diskon_mulai' => 'required|date',
            'diskon_selesai' => 'required|date|after_or_equal:diskon_mulai',
        ]);

        try {
            DB::beginTransaction();

            $barang->update($data);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Terjadi masalah saat mengubah diskon. '.$th->getMessage());
        }

        return redirect()->back()->with('success', 'Berhasil mengubah diskon barang!');
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
        if ($kategori->barangs->count() > 0) {
            return redirect()->back()->with('error', 'Data tidak bisa dihapus karena masih memiliki barang terkait');
        }

        $kategori->delete();

        return redirect()->back()->with('success', 'Data berhasil dihapus');
    }

    public function get_type(Request $request)
    {
        $data = BarangType::where('barang_unit_id', $request->unit_id)->get();

        if ($data->isEmpty()) {
            return response()->json([
                'status' => 0,
                'message' => 'Unit belum memiliki type!!',
            ]);
        }

        return response()->json([
            'status' => 1,
            'data' => $data,
        ]);
    }

    public function get_barang_nama(Request $request)
    {
        $data = BarangNama::where('barang_kategori_id', $request->kategori_id)->get();

        if ($data->isEmpty()) {
            return response()->json([
                'status' => 0,
                'message' => 'Kategori belum memiliki nama barang!!',
            ]);
        }

        return response()->json([
            'status' => 1,
            'data' => $data,
        ]);
    }

    public function edit_stok_su(BarangStokHarga $stok, Request $request)
    {
        $data = $request->validate([
            'stok_awal' => 'required',
            'stok' => 'required',
            'harga_beli' => 'required'
        ]);

        if (auth()->user()->role != 'su') {
            return redirect()->back()->with('error', 'Anda tidak punya wewenang untuk eksekusi ini!!');
        }

        try {
            DB::beginTransaction();

            $stok->update([
                'stok_awal' => $data['stok_awal'],
                'stok' => $data['stok'],
                'harga_beli' => $data['harga_beli'],
            ]);

            DB::commit();

        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();

            return redirect()->back()->with('error', 'Terjadi Kesalahan');
        }

        return redirect()->back()->with('success', 'Data Berhasil Di update!!');
    }

    public function stok_data(Request $request)
    {
        // $filters = $request->only(['area', 'kecamatan', 'kode_toko', 'status']); // Ambil filter dari request

        $data = BarangStokHarga::query()->with(['barang' => function ($query) {
                $query->with(['type', 'kategori', 'barang_nama', 'satuan', 'unit']);
            }])
            ->where('hide', 0)
            ->orderBy('barang_unit_id')
            ->orderBy('barang_type_id')
            ->orderBy('barang_kategori_id')
            ->orderBy('barang_nama_id')
            ->orderBy('barang_id')
            ->orderBy('stok');

        return DataTables::of($data)
            ->make(true);

    }

    public function stok_ppn(Request $request)
    {
        $kategori = BarangKategori::with(['barang_nama'])->get();
        $type = BarangType::with(['unit', 'barangs'])->get();
        $ppnRate = Pajak::where('untuk', 'ppn')->first()->persen;

        $unitFilter = $request->input('unit');
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

        $jenis = 1;

        $data = $db->barangStokV3($jenis, $unitFilter, $typeFilter, $kategoriFilter, $barangNamaFilter);
        $units = BarangUnit::all();
        $karyawan = Karyawan::where('status', 1)->get();

        return view('db.stok-ppn.index', [
            'data' => $data,
            'kategori' => $kategori,
            'units' => $units,
            'type' => $type,
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

    public function stok_harga_update(Request $request, BarangStokHarga $barang)
    {
        $data = $request->validate([
            'harga' => 'required',
            'min_jual' => 'required',
        ]);

        $data['harga'] = str_replace('.', '', $data['harga']);
        $data['min_jual'] = str_replace('.', '', $data['min_jual']);

        if (auth()->user()->role != 'su') {
           if ($data['harga'] < $barang->harga_beli) {
                return redirect()->back()->with('error', 'Harga jual tidak boleh lebih kecil dari harga beli!');
            }
        }


        if ($data['min_jual'] <= 0) {
            return redirect()->back()->with('error', 'Minimum kelipatan jual tidak boleh dibawah 1 !');
        }

        $barang->update($data);

        return redirect()->back()->with('success', 'Berhasil mengubah data harga!');
    }

    public function stok_non_ppn(Request $request)
    {
        // $kategori = BarangKategori::with(['barang_nama'])->get();
        // $type = BarangType::with(['unit', 'barangs'])->get();

        $unitFilter = $request->input('unit');
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

        return view('db.stok-non-ppn.index', [
            'data' => $data,
            // 'kategori' => $kategori,
            'units' => $units,
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

    public function stok_ppn_download(Request $request)
    {
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        $ppnRate = Pajak::where('untuk', 'ppn')->first()->persen;

        $unitFilter = $request->input('unit');
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

        $jenis = 1;

        $data = $db->barangStokPdf($jenis, $unitFilter, $typeFilter, $kategoriFilter, $barangNamaFilter);

        $pdf = PDF::loadview('db.stok-ppn.pdf', [
            'data' => $data,
            'unitFilter' => $unitFilter,
            'typeFilter' => $typeFilter,
            'kategoriFilter' => $kategoriFilter,
            'selectType' => $selectType,
            'selectKategori' => $selectKategori,
            'ppnRate' => $ppnRate,
            'barangNamaFilter' => $barangNamaFilter,
            'selectBarangNama' => $selectBarangNama,
        ])
            ->setPaper('a4', 'landscape');
        $tanggal = date('d-m-Y');

        return $pdf->stream('StokPpn-'.$tanggal.'.pdf');
    }

    public function stok_non_ppn_download(Request $request)
    {
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        $ppnRate = Pajak::where('untuk', 'ppn')->first()->persen;

        $unitFilter = $request->input('unit');
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

        $data = $db->barangStokPdf($jenis, $unitFilter, $typeFilter, $kategoriFilter, $barangNamaFilter);

        $pdf = PDF::loadview('db.stok-non-ppn.pdf', [
            'data' => $data,
            'unitFilter' => $unitFilter,
            'typeFilter' => $typeFilter,
            'kategoriFilter' => $kategoriFilter,
            'selectType' => $selectType,
            'selectKategori' => $selectKategori,
            'ppnRate' => $ppnRate,
            'barangNamaFilter' => $barangNamaFilter,
            'selectBarangNama' => $selectBarangNama,
        ])
            ->setPaper('a4', 'landscape');
        $tanggal = date('d-m-Y');

        return $pdf->stream('StokNonPpn-'.$tanggal.'.pdf');
    }

    public function ganti_rugi(BarangStokHarga $stok, Request $request)
    {
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        $data = $request->validate([
            'harga_beli_dpp_act' => 'required',
            'karyawan_id' => 'required|exists:karyawans,id',
            'kas_ppn' => 'required|in:0,1',
            'jumlah_hilang' => 'required',
            'aksi' => 'required|in:1,2',
        ]);

        if ($data['jumlah_hilang'] <= 0 || $data['jumlah_hilang'] > $stok->stok) {
            $errorMessage = $data['jumlah_hilang'] <= 0 ? 'Jumlah hilang tidak boleh kurang dari atau sama dengan 0!' : 'Jumlah hilang tidak boleh lebih besar dari stok!';

            return redirect()->back()->with('error', $errorMessage);
        }

        $data['jumlah'] = str_replace('.', '', $data['jumlah_hilang']);
        $data['harga'] = str_replace('.', '', $data['harga_beli_dpp_act']);
        $data['total'] = $data['jumlah'] * $data['harga'];
        $data['lunas'] = $data['aksi'] == 1 ? 1 : 0;
        $data['total_bayar'] = $data['lunas'] == 1 ? $data['total'] : 0;
        $data['sisa'] = $data['lunas'] == 1 ? 0 : $data['total'];
        $data['barang_stok_harga_id'] = $stok->id;
        $data['barang_id'] = $stok->barang_id;

        unset($data['jumlah_hilang'], $data['harga_beli_dpp_act'], $data['aksi']);

        $db = new GantiRugi;

        $res = $db->ganti_rugi($data);

        return redirect()->back()->with($res['status'], $res['message']);

    }

    public function stok_history(Request $request)
    {
        $barang = Barang::find($request->barang);

        $data = BarangStokHarga::with(['barang.satuan'])->where('barang_id', $barang->id)->orderBy('created_at', 'desc')->get();

        if ($data->isEmpty()) {
            return response()->json([
                'status' => 0,
                'message' => 'Data stok tidak ditemukan!',
            ]);
        }

        return response()->json([
            'status' => 1,
            'data' => $data,
            'keterangan' => $barang->keterangan,
        ]);
    }

    public function hide_stok(BarangStokHarga $barang)
    {
        // check if there is minimum 1 barang->barang_id that hide = 0
        $barangCheck = BarangStokHarga::where('barang_id', $barang->barang_id)->where('hide', 0)->count();

        if ($barangCheck == 1) {
            return redirect()->back()->with('error', 'Data stok tidak bisa disembunyikan karena minimal harus ada 1 data stok yang tidak disembunyikan!');
        }

        if ($barang->stok > 0) {
            return redirect()->back()->with('error', 'Data stok tidak bisa disembunyikan karena masih memiliki stok!');
        }

        $barang->update([
            'hide' => 1,
        ]);

        return redirect()->back()->with('success', 'Berhasil menyembunyikan data stok!');
    }

    public function subpg(Request $request)
    {
        $data = Subpg::select('id', 'nama')->get();

        return view('db.subpg.index', [
            'data' => $data,
        ]);
    }

    public function subpg_store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|unique:subpgs,nama',
        ]);

        $this->storeDb(Subpg::class, $data);

        return redirect()->back()->with('success', 'Data berhasil ditambahkan');
    }

    public function subpg_update(Request $request, Subpg $subpg)
    {
        $data = $request->validate([
            'nama' => 'required|unique:subpgs,nama,'.$subpg->id.',id',
        ]);

        $subpg->update($data);

        return redirect()->back()->with('success', 'Data berhasil diubah');
    }

    public function subpg_destroy(Subpg $subpg)
    {
        if ($subpg->barang->count() > 0) {
            return redirect()->back()->with('error', 'Data tidak bisa dihapus karena masih memiliki barang terkait');
        }

        $subpg->delete();

        return redirect()->back()->with('success', 'Data berhasil dihapus');
    }
}
