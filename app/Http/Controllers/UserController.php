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
use App\Models\db\KonsumenDoc;
use App\Models\db\Pajak;
use App\Models\Wilayah;
use App\Services\CustomerFileService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

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

        $db = new BarangStokHarga;

        $data = $db->barangStokAll($unitFilter, $typeFilter, $kategoriFilter, $barangNamaFilter);
        $ppnRate = Pajak::where('untuk', 'ppn')->first()->persen;
        $units = BarangUnit::all();
        $karyawan = Karyawan::where('status', 1)->get();

        return view('user.stok-all.index', [
            'data' => $data,
            'ppnRate' => $ppnRate,
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

    public function konsumen_data(Request $request)
    {
        $filters = $request->only(['area', 'kecamatan', 'kode_toko', 'status']); // Ambil filter dari request

        $data = Konsumen::query()->with(['kode_toko', 'provinsi', 'kabupaten_kota', 'kecamatan', 'karyawan'])
            ->withCount('docs')
            ->filter($filters);

        // Ambil semua no_hp yang duplikat sekaligus
        $duplicateNoHp = Konsumen::select('no_hp')
            ->groupBy('no_hp')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('no_hp')
            ->toArray();

        return DataTables::of($data)
            ->addColumn('full_kode', fn ($d) => $d->full_kode)
            ->addColumn('limit_plafon', fn ($d) => $d->nf_plafon)
            ->addColumn('kode_toko', fn ($d) => $d->kode_toko->kode ?? '')
            // ->addColumn('nama', fn($d) => $d->nama)
            ->addColumn('cp', function ($d) use ($duplicateNoHp) {
                $noHpWarning = (substr_count($d->no_hp, ' ') > 0 || in_array($d->no_hp, $duplicateNoHp)) ? 'text-danger' : '';

                return '<ul><li>CP: '.e($d->cp).'</li><li class="'.$noHpWarning.'">No.HP: '.e($d->no_hp).'</li><li>No.Kantor: '.e($d->no_kantor ?? '-').'</li></ul>';
            })
            ->addColumn('pembayaran_raw', function ($d) {
                return "
                    $d->sistem_pembayaran <br>
                    (".($d->pembayaran == 2 ? $d->tempo_hari.' Hari' : '').')
                ';
            })
            ->addColumn('ktp', function ($d) {
                return view('user.konsumen._ktp', compact('d'))->render();
            })
            ->addColumn('diskon', function ($d) {
                return view('user.konsumen._diskon', compact('d'))->render();
            })
            ->addColumn('dokumen', function ($d) {
                return view('user.konsumen._dokumen', compact('d'))->render();
            })
            ->rawColumns(['cp', 'ktp', 'pembayaran_raw', 'diskon', 'dokumen'])
            ->make(true);
    }

    public function konsumen_dokumen(Request $request)
    {
        $input = $request->validate(['konsumen_id' => ['required', 'integer']]);
        $konsumen = Konsumen::withCount('docs')->find($input['konsumen_id']);

        if (! $konsumen) {
            return response()->json(['status' => 'error', 'message' => 'Konsumen tidak ditemukan'], 404);
        }

        $docs = $konsumen->docs->load('barang_unit');

        return response()->json([
            'status' => 'success',
            'data' => $docs,
            'konsumen' => $konsumen,
        ]);
    }

    public function konsumen_dokumen_destroy(KonsumenDoc $dokumen, CustomerFileService $files)
    {
        try {
            $files->deleteDocument($dokumen);

            return response()->json(['status' => 'success', 'message' => 'Dokumen berhasil dihapus']);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json(['status' => 'error', 'message' => 'Dokumen belum dapat dihapus. Silakan coba lagi.'], 500);
        }
    }

    public function konsumen_dokumen_store(Request $request, CustomerFileService $files)
    {
        $data = $request->validate([
            'konsumen_id' => 'required|integer|exists:konsumens,id',
            'nama' => 'required|string|max:255',
            'barang_unit_id' => 'nullable|integer|exists:barang_units,id',
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120', // Maks 5MB
        ]);

        unset($data['file']);
        try {
            $files->storeDocument($data, $request->file('file'));

            return response()->json(['status' => 'success', 'message' => 'Dokumen berhasil diunggah']);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json(['status' => 'error', 'message' => 'Dokumen belum dapat disimpan. Silakan coba lagi.'], 500);
        }
    }

    public function konsumen(Request $request)
    {
        // $filters = $request->only(['area', 'kecamatan', 'kode_toko', 'status']); // Ambil filter dari request

        // $data = Konsumen::with(['provinsi', 'kabupaten_kota', 'kecamatan', 'sales_area', 'kode_toko', 'karyawan'])
        //     ->filter($filters) // Gunakan scope filter
        //     // ->limit(10)
        //     ->get();

        $kecamatan_filter = Wilayah::whereIn('id_induk_wilayah', function ($query) {
            $query->select('id_wilayah')
                ->from('wilayahs')
                ->where('id_induk_wilayah', '110000');
        })->where('id_level_wilayah', 3)->get();

        $provinsi = Wilayah::where('id_level_wilayah', 1)->get();

        $sales_area = Karyawan::with('jabatan')->whereHas('jabatan', function ($query) {
            $query->where('is_sales', 1);
        })->select('id', 'nama')->get();

        return view('user.konsumen.index', [
            // 'data' => $data,
            'provinsi' => $provinsi,
            'sales_area' => $sales_area,
            'kode_toko' => KodeToko::select('id', 'kode')->get(),
            'kecamatan_filter' => $kecamatan_filter,
        ]);
    }
}
