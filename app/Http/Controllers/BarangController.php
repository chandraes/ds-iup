<?php

namespace App\Http\Controllers;

use App\Models\db\Barang\Barang;
use App\Models\db\Barang\BarangKategori;
use App\Models\db\Barang\BarangNama;
use App\Models\db\Barang\BarangStokHarga;
use App\Models\db\Barang\BarangType;
use App\Models\db\Barang\BarangUnit;
use App\Models\db\Karyawan;
use App\Models\db\Pajak;
use App\Models\db\Satuan;
use App\Models\GantiRugi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;

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
        $data = BarangKategori::with(['barang_nama'])->withCount('barang_nama')->orderBy('urut')->get();

        return view('db.kategori-barang.index', [
            'data' => $data
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
            'nama' => 'required|unique:barang_units,nama',
        ]);

        $this->storeDb(BarangUnit::class, $data);

        return redirect()->back()->with('success', 'Data berhasil ditambahkan');
    }

    public function unit_update(Request $request, BarangUnit $unit)
    {
        $data = $request->validate([
           'nama' => 'required|unique:barang_units,nama,' . $unit->id . ',id',
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
        if($type->barangs->count() > 0) return redirect()->back()->with('error', 'Data tidak bisa dihapus karena masih memiliki barang terkait');

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
        $barangNamaFilter = $request->input('barang_nama');

        if (!empty($unitFilter) && $unitFilter != '') {
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
            $selectType = BarangType::all();
            $selectKategori = $kategoriDb->get();
            $selectBarangNama = BarangNama::select('nama')->distinct()->orderBy('nama')->get();
        }

        $db = new BarangUnit();

        $units = $db->barangAll($unitFilter, $typeFilter, $kategoriFilter, $jenisFilter, $barangNamaFilter);
        $kategori = $kategoriDb->with('barang_nama')->get();

        $satuan = Satuan::all();

        return view('db.barang.index', [
            // 'data' => $data,
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

    public function barang_v2(Request $request)
    {
        $data = BarangUnit::with(['types', 'types.barangs', 'types.barangs.detail_types', 'types.barangs.detail_types.type'])->get();

        return view('db.barang.index', [
            'data' => $data
        ]);
    }

    public function barang_store(Request $request)
    {
        $data = $request->validate([
            'barang_type_id' => 'required|exists:barang_types,id',
            'barang_kategori_id' => 'required|exists:barang_kategoris,id',
            'barang_nama_id' => 'required|exists:barang_namas,id',
            'satuan_id' => 'required|exists:satuans,id',
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
            'detail_type' => 'nullable|array',
        ]);

        try {

            DB::beginTransaction();
            $detailType = null;
            if (isset($data['detail_type'])) {
                $detailType = $data['detail_type'];
                unset($data['detail_type']);
            }
            $data['barang_unit_id'] = BarangType::find($data['barang_type_id'])->barang_unit_id;
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
            'jenis' => 'required|in:1,2',
            'kode' => 'nullable',
            'merk' => [
                    'required',
                    // Rule::unique('barangs')->where(function ($query) use ($request) {
                    //     return $query->where('barang_type_id', $request->barang_type_id)
                    //                 ->where('barang_kategori_id', $request->barang_kategori_id)
                    //                 ->where('barang_nama_id', $request->barang_nama_id)
                    //                 ->where('jenis', $request->jenis)
                    //                 ->where('satuan_id', $request->satuan_id)
                    //                 ->where('merk', $request->merk)
                    //                 ->where('kode', $request->kode);
                    // })->ignore($barang->id, 'id'),
                ],
            'detail_type' => 'nullable|array',
            ]);

            try {
                DB::beginTransaction();
                $detailType = null;
                if(isset($data['detail_type'])){
                    $detailType = $data['detail_type'];
                    unset($data['detail_type']);
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

                DB::commit();

            } catch (\Throwable $th) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Terjadi masalah saat mengubah data. '.$th->getMessage());
            }


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
        // $kategori = BarangKategori::with(['barang_nama'])->get();
        // $type = BarangType::with(['unit', 'barangs'])->get();
        $ppnRate = Pajak::where('untuk', 'ppn')->first()->persen;

        $unitFilter = $request->input('unit');
        $typeFilter = $request->input('type');
        $kategoriFilter = $request->input('kategori');
        $barangNamaFilter = $request->input('barang_nama');

        if (!empty($unitFilter) && $unitFilter != '') {
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
        $units = BarangUnit::all();
        $karyawan = Karyawan::where('status', 1)->get();

        return view('db.stok-ppn.index', [
            'data' => $data,
            // 'kategori' => $kategori,
            'units' => $units,
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
        // $kategori = BarangKategori::with(['barang_nama'])->get();
        // $type = BarangType::with(['unit', 'barangs'])->get();

        $unitFilter = $request->input('unit');
        $typeFilter = $request->input('type');
        $kategoriFilter = $request->input('kategori');
        $barangNamaFilter = $request->input('barang_nama');

        if (!empty($unitFilter) && $unitFilter != '') {
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

        if (!empty($unitFilter) && $unitFilter != '') {
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

        if (!empty($unitFilter) && $unitFilter != '') {
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

        $db = new GantiRugi();

        $res = $db->ganti_rugi($data);

        return redirect()->back()->with($res['status'], $res['message']);

    }

    public function stok_history(Request $request)
    {
        $barang = Barang::find($request->barang);

        $data = BarangStokHarga::where('barang_id', $barang->id)->orderBy('created_at', 'desc')->get();

        if ($data->isEmpty()) {
            return response()->json([
                'status' => 0,
                'message' => 'Data stok tidak ditemukan!'
            ]);
        }

        return response()->json([
            'status' => 1,
            'data' => $data
        ]);
    }

}
