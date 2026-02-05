<?php

namespace App\Http\Controllers;

use App\Models\db\Barang\Barang;
use App\Models\db\Barang\BarangKategori;
use App\Models\db\Barang\BarangNama;
use App\Models\db\Barang\BarangStokHarga;
use App\Models\db\Barang\BarangType;
use App\Models\db\Barang\BarangUnit;
use App\Models\db\Karyawan;
use App\Models\db\KodeToko;
use App\Models\db\Konsumen;
use App\Models\db\Pajak;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Illuminate\Support\LazyCollection;
use App\Models\transaksi\InvoiceJualDetail;
use App\Models\Wilayah;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PerusahaanController extends Controller
{
    public function konsumen(Request $request)
    {
        $filters = $request->only(['area', 'kabupaten_kota','kecamatan', 'kode_toko', 'status', 'provinsi']); // Ambil filter dari request
        $wilayah = Wilayah::whereIn('id_level_wilayah', [1, 2, 3])->get()->groupBy('id_level_wilayah');
        $provinsi = $wilayah->get(1, collect());

        if ($request->has('provinsi') && $request->input('provinsi') != '') {
            $prov = Wilayah::find($request->input('provinsi'));
            $kabupaten_kota = Wilayah::where('id_induk_wilayah', $prov->id_wilayah)->where('id_level_wilayah', 2)->get();
        } else {
            $kabupaten_kota = $wilayah->get(2, collect());
        }

        if ($request->has('kabupaten_kota') && $request->input('kabupaten_kota') != '') {
            $kab = Wilayah::find($request->input('kabupaten_kota'));
            $kecamatan_filter = Wilayah::where('id_induk_wilayah', $kab->id_wilayah )->where('id_level_wilayah', 3)->get();
        } else {
            $kecamatan_filter = $wilayah->get(3, collect());
        }

        $sales_area = Karyawan::with('jabatan')->whereHas('jabatan', function ($query) {
            $query->where('is_sales', 1);
        })->select('id', 'nama')->get();

        return view('perusahaan.konsumen', [
            'provinsi' => $provinsi,
            'kabupaten_kota' => $kabupaten_kota,
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
        $filters = $request->only(['area', 'kecamatan', 'kode_toko', 'status', 'provinsi', 'kabupaten_kota', 'provinsi']);

        $query = Konsumen::with(['provinsi', 'kabupaten_kota', 'kecamatan', 'sales_area', 'kode_toko', 'karyawan'])
            ->filter($filters);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%$search%")
                ->orWhere('alamat', 'like', "%$search%");
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

    public function exportKonsumen(Request $request)
    {
        // 1. Set Time Limit agar tidak timeout jika data sangat besar (misal: 0 = unlimited)
        set_time_limit(0);
        ini_set('memory_limit', '512M'); // Opsional, sesuaikan dengan server

        // 2. Ambil filter (Sama persis dengan logic di function konsumen_data)
        $filters = $request->only(['area', 'kecamatan', 'kode_toko', 'status', 'provinsi', 'kabupaten_kota']);
        $search = $request->input('search');

        // 3. Bangun Query
        $query = Konsumen::with(['provinsi', 'kabupaten_kota', 'kecamatan', 'sales_area', 'kode_toko', 'karyawan'])
            ->filter($filters);

        // Jika ada pencarian keyword (opsional, jika ingin fitur search ikut ter-export)
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%$search%")
                ->orWhere('alamat', 'like', "%$search%");
            });
        }

        // 4. Buat Stream Download
        // Nama file disesuaikan dengan tanggal agar unik
        $fileName = 'Data_Konsumen_' . date('Y-m-d_H-i-s') . '.xlsx';

        $writer = SimpleExcelWriter::streamDownload($fileName);

        // 5. Gunakan cursor() untuk menghemat memori
        // Query tidak dieksekusi sekaligus, tapi row-per-row saat looping
        $query->cursor()->each(function ($d) use ($writer) {
            // Format data per baris
            $row = [
                'KODE'          => $d->full_kode,
                'KODE TOKO'     => $d->kode_toko ? $d->kode_toko->kode : '',
                'NAMA'          => $d->nama,
                'SALES AREA'         => $d->karyawan ? $d->karyawan->nama : '', // Asumsi relasi karyawan adalah sales
                'PROVINSI'      => $d->provinsi ? $d->provinsi->nama_wilayah : '',
                'KAB/KOTA'      => $d->kabupaten_kota ? $d->kabupaten_kota->nama_wilayah : '',
                'KECAMATAN'     => $d->kecamatan ? $d->kecamatan->nama_wilayah : '',
                'ALAMAT'        => $d->alamat,
            ];

            // Tulis baris ke stream excel
            $writer->addRow($row);
        });

        return $writer->toBrowser();
    }

    public function sales(Request $request)
    {
         $data = Karyawan::with('jabatan')->where('status', 1)->whereHas('jabatan', function ($query) {
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

        $unitFilter = Auth::user()->barang_unit_id;
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
        $unitFilter = Auth::user()->barang_unit_id;
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

    public function selling_out(Request $request)
    {
        $filters = $request->only(['area', 'kabupaten_kota', 'kecamatan', 'kode_toko', 'status', 'provinsi', 'month', 'year', 'sales', 'barang_nama_id']);
        $db = new InvoiceJualDetail();

        $month = $request->input('month') ?? date('m');
        $year = $request->input('year') ?? date('Y');

        $perusahaan = Auth::user()->barang_unit_id;

        if (empty($perusahaan) || $perusahaan == '') {
            return redirect()->back()->with('error', 'Perusahaan tidak ditemukan atau tidak valid. Silahkan hubungi administrator sistem.');
        }

        $dataTahun = $db->dataTahun();

        $kab_kot = Konsumen::select('kabupaten_kota_id')->distinct()->get();

        $kabupaten_kota = Wilayah::select('id', 'nama_wilayah')->whereIn('id', $kab_kot->pluck('kabupaten_kota_id'))->get();

        // create array of month in indonesian with key 1-12
        $dataBulan = [
            '1' => 'Januari',
            '2' => 'Februari',
            '3' => 'Maret',
            '4' => 'April',
            '5' => 'Mei',
            '6' => 'Juni',
            '7' => 'Juli',
            '8' => 'Agustus',
            '9' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember'
        ];


        $data = $db->sellingOut($month, $year, $perusahaan, $filters);
        $ppn = Pajak::where('untuk', 'ppn')->first()->persen;

        $sales_area = Karyawan::with('jabatan')->whereHas('jabatan', function ($query) {
            $query->where('is_sales', 1);
        })->select('id', 'nama')->get();

        $barang_nama = Barang::select('barang_nama_id')->distinct()->get();

        $filterBarangNama = BarangNama::select('id', 'nama')
            ->whereIn('id', $barang_nama->pluck('barang_nama_id'))
            ->orderBy('nama')
            ->get();

        return view('perusahaan.selling-out.index', [
            'data' => $data,
            'ppn' => $ppn,
            'dataTahun' => $dataTahun,
            'dataBulan' => $dataBulan,
            'sales' => $sales_area,
            'kabupaten_kota' => $kabupaten_kota,
            'filterBarangNama' => $filterBarangNama,
        ]);
    }

    public function order(Request $request)
    {
        $selectUnit = BarangUnit::select('id', 'nama')->where('id', auth()->user()->barang_unit_id)->get();
        $selectBidang = BarangType::select('id', 'nama')->get();
        $selectKategori = BarangKategori::all();
        $selectBarangNama = BarangNama::select('id', 'nama')->distinct()->orderBy('id')->get();
        return view('perusahaan.order.index', compact('selectKategori', 'selectBarangNama', 'selectUnit', 'selectBidang'));
    }

    public function order_data(Request $request)
    {
        // 0. Ambil Input Multiplier (Default 2 jika kosong)
        $multiplier = $request->input('multiplier');
        if(is_null($multiplier) || $multiplier === '') {
            $multiplier = 2;
        } else {
            // Pastikan angka untuk keamanan
            $multiplier = (float) $multiplier;
        }

        // 1. Definisikan Subquery untuk 'stok_ready'
        $stokSubquery = DB::table('barang_stok_hargas')
            ->selectRaw('COALESCE(SUM(stok), 0)')
            ->whereColumn('barang_id', 'barangs.id')
            ->where('stok', '>', 0);

        // 2. Definisikan Subquery untuk 'avg_sales'
        $avgSubquery = DB::table('invoice_jual_details as ijd')
            ->join('invoice_juals as ij', 'ijd.invoice_jual_id', '=', 'ij.id')
            ->whereColumn('ijd.barang_id', 'barangs.id')
            ->where('ij.void', 0)
            ->selectRaw('COALESCE(SUM(ijd.jumlah), 0) / GREATEST(TIMESTAMPDIFF(MONTH, MIN(ij.created_at), NOW()), 1)');

        // 3. QUERY UTAMA
        $query = Barang::with(['unit', 'type', 'barang_nama', 'satuan', 'kategori'])
            ->where('barangs.is_active', 1)
            ->select('barangs.*')
            ->selectSub($stokSubquery, 'stok_ready')
            ->selectSub($avgSubquery, 'avg_sales')
            ->leftJoin('barang_namas', 'barangs.barang_nama_id', '=', 'barang_namas.id')
            ->leftJoin('barang_kategoris', 'barangs.barang_kategori_id', '=', 'barang_kategoris.id')
            ->leftJoin('barang_types', 'barangs.barang_type_id', '=', 'barang_types.id')
            ->leftJoin('barang_units', 'barangs.barang_unit_id', '=', 'barang_units.id')
            ->orderBy('barang_units.nama', 'asc')
            ->orderBy('barang_types.nama', 'asc')
            ->orderBy('barang_kategoris.nama', 'asc')
            ->orderBy('barang_namas.nama', 'asc');

        // 4. FILTERING STANDAR

        $query->where('barangs.barang_unit_id', auth()->user()->barang_unit_id);

        if ($request->filled('bidang')) {
            $query->where('barangs.barang_type_id', $request->input('bidang'));
        }
        if ($request->filled('kategori')) {
            $query->where('barangs.barang_kategori_id', $request->input('kategori'));
        }
        if ($request->filled('barang_nama')) {
            $query->where('barangs.barang_nama_id', $request->input('barang_nama'));
        }
        if ($request->filled('jenis')) {
            $query->where('barangs.jenis', $request->input('jenis'));
        }

        // 5. FILTER KHUSUS (Menggunakan Variabel $multiplier)
        // Rumus: (Avg * Multiplier) - Stok > 0
        // Karena kita inject variable ke string SQL, pastikan variable aman (sudah di-cast float diatas)
        $query->havingRaw("((avg_sales * {$multiplier}) - stok_ready) >= 1");

        // 6. DATATABLES CONFIG
        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('barang_ppn', function ($row) {
                return $row->jenis == 1 ? 'Ya' : 'Tidak';
            })
            // Kolom Stok
            ->addColumn('stok_info', function ($row) {
                return number_format($row->stok_ready, 0, ',', '.');
            })
            ->orderColumn('stok_info', 'stok_ready $1')

            // Kolom Avg
            ->addColumn('avg_penjualan', function ($row) {
                return number_format($row->avg_sales, 0, ',', '.');
            })
            ->orderColumn('avg_penjualan', 'avg_sales $1')

            // Kolom Saran Order (Dinamis berdasarkan multiplier)
            ->addColumn('saran_order', function ($row) use ($multiplier) {
                return number_format($row->avg_sales * $multiplier, 0, ',', '.');
            })
            ->orderColumn('saran_order', "(avg_sales * {$multiplier}) $1")

            // Kolom Order Qty (Dinamis berdasarkan multiplier)
            ->addColumn('order_qty', function ($row) use ($multiplier) {
                $qty = ($row->avg_sales * $multiplier) - $row->stok_ready;
                return number_format($qty, 0, ',', '.');
            })
            ->orderColumn('order_qty', "((avg_sales * {$multiplier}) - stok_ready) $1")

            ->rawColumns(['nama_barang'])
            ->make(true);
    }

    public function order_export_pdf(Request $request)
    {
        $request->validate([
            'unit' => 'required|exists:barang_units,id'
        ], [
            'unit.required' => ' Silahkan Melakukan Filter Perusahaan Terlebih Dahulu!!',
            'unit.exists' => 'Perusahaan yang dipilih tidak valid'
        ]);
        // --- LOGIKA QUERY SAMA PERSIS DENGAN ORDER_DATA ---
        // Kita ulangi query builder disini agar hasil PDF sama persis dengan tabel

        $multiplier = $request->input('multiplier');
        if(is_null($multiplier) || $multiplier === '') {
            $multiplier = 2;
        } else {
            $multiplier = (float) $multiplier;
        }

        $stokSubquery = DB::table('barang_stok_hargas')
            ->selectRaw('COALESCE(SUM(stok), 0)')
            ->whereColumn('barang_id', 'barangs.id')
            ->where('stok', '>', 0);

        $avgSubquery = DB::table('invoice_jual_details as ijd')
            ->join('invoice_juals as ij', 'ijd.invoice_jual_id', '=', 'ij.id')
            ->whereColumn('ijd.barang_id', 'barangs.id')
            ->where('ij.void', 0)
            ->selectRaw('COALESCE(SUM(ijd.jumlah), 0) / GREATEST(TIMESTAMPDIFF(MONTH, MIN(ij.created_at), NOW()), 1)');

        $query = Barang::with(['unit', 'type', 'barang_nama', 'satuan', 'kategori'])
            ->where('barangs.is_active', 1)
            ->select('barangs.*')
            ->orderBy('barangs.barang_nama_id', 'asc')
            ->selectSub($stokSubquery, 'stok_ready')
            ->selectSub($avgSubquery, 'avg_sales')
            ->leftJoin('barang_namas', 'barangs.barang_nama_id', '=', 'barang_namas.id')
            ->leftJoin('barang_kategoris', 'barangs.barang_kategori_id', '=', 'barang_kategoris.id')
            ->leftJoin('barang_types', 'barangs.barang_type_id', '=', 'barang_types.id')
            ->leftJoin('barang_units', 'barangs.barang_unit_id', '=', 'barang_units.id')
            ->orderBy('barang_units.nama', 'asc')
            ->orderBy('barang_types.nama', 'asc')
            ->orderBy('barang_kategoris.nama', 'asc')
            ->orderBy('barang_namas.nama', 'asc');

        $query->where('barangs.barang_unit_id', auth()->user()->barang_unit_id);

        if ($request->filled('bidang')) {
            $query->where('barangs.barang_type_id', $request->input('bidang'));
        }
        if ($request->filled('kategori')) {
            $query->where('barangs.barang_kategori_id', $request->input('kategori'));
        }
        if ($request->filled('barang_nama')) {
            $query->where('barangs.barang_nama_id', $request->input('barang_nama'));
        }
        if ($request->filled('jenis')) {
            $query->where('barangs.jenis', $request->input('jenis'));
        }

        $query->havingRaw("((avg_sales * {$multiplier}) - stok_ready) >= 1");

        // Urutkan default (misal berdasarkan Nama Barang atau Order Qty terbesar)
        $query->orderByRaw("((avg_sales * {$multiplier}) - stok_ready) DESC");

        // Ambil Data (Get, bukan DataTables)
        $data = $query->get();

        if (count($data) == 0) {
            return redirect()->back()->with('error', 'Tidak ada data untuk diekspor. Silahkan sesuaikan filter Anda.');
        }

        $perusahaan = BarangUnit::find($request->input('unit'));
        // Load View PDF
        $pdf = Pdf::loadView('db.order.pdf', compact('data', 'multiplier', 'perusahaan'));

        // Set Paper Size (Optional)
        $pdf->setPaper('a4', 'landscape');

        return $pdf->stream('laporan_saran_order.pdf');
    }

    // Tambahkan method ini di dalam class DatabaseController
    public function export_excel(Request $request)
    {
        $request->validate([
            'unit' => 'required|exists:barang_units,id'
        ], [
            'unit.required' => ' Silahkan Melakukan Filter Perusahaan Terlebih Dahulu!!',
            'unit.exists' => 'Perusahaan yang dipilih tidak valid'
        ]);
        // set_time_limit(0);
        // ini_set('memory_limit', '-1');
        if (ob_get_contents()) ob_end_clean();

        $multiplier = $request->input('multiplier');
        if(is_null($multiplier) || $multiplier === '') {
            $multiplier = 2;
        } else {
            $multiplier = (float) $multiplier;
        }

        $stokSubquery = DB::table('barang_stok_hargas')
            ->selectRaw('COALESCE(SUM(stok), 0)')
            ->whereColumn('barang_id', 'barangs.id')
            ->where('stok', '>', 0);

        $avgSubquery = DB::table('invoice_jual_details as ijd')
            ->join('invoice_juals as ij', 'ijd.invoice_jual_id', '=', 'ij.id')
            ->whereColumn('ijd.barang_id', 'barangs.id')
            ->where('ij.void', 0)
            ->selectRaw('COALESCE(SUM(ijd.jumlah), 0) / GREATEST(TIMESTAMPDIFF(MONTH, MIN(ij.created_at), NOW()), 1)');

        $query = Barang::with(['unit', 'type', 'barang_nama', 'satuan', 'kategori'])
            ->where('barangs.is_active', 1)
            ->select('barangs.*')
            ->orderBy('barangs.barang_nama_id', 'asc')
            ->selectSub($stokSubquery, 'stok_ready')
            ->selectSub($avgSubquery, 'avg_sales')
            ->leftJoin('barang_namas', 'barangs.barang_nama_id', '=', 'barang_namas.id')
            ->leftJoin('barang_kategoris', 'barangs.barang_kategori_id', '=', 'barang_kategoris.id')
            ->leftJoin('barang_types', 'barangs.barang_type_id', '=', 'barang_types.id')
            ->leftJoin('barang_units', 'barangs.barang_unit_id', '=', 'barang_units.id')
            ->orderBy('barang_units.nama', 'asc')
            ->orderBy('barang_types.nama', 'asc')
            ->orderBy('barang_kategoris.nama', 'asc')
            ->orderBy('barang_namas.nama', 'asc');

        $query->where('barangs.barang_unit_id', auth()->user()->barang_unit_id);

        if ($request->filled('bidang')) {
            $query->where('barangs.barang_type_id', $request->input('bidang'));
        }
        if ($request->filled('kategori')) {
            $query->where('barangs.barang_kategori_id', $request->input('kategori'));
        }
        if ($request->filled('barang_nama')) {
            $query->where('barangs.barang_nama_id', $request->input('barang_nama'));
        }
        if ($request->filled('jenis')) {
            $query->where('barangs.jenis', $request->input('jenis'));
        }

        $query->havingRaw("((avg_sales * {$multiplier}) - stok_ready) >= 1");

        // Urutkan default (misal berdasarkan Nama Barang atau Order Qty terbesar)
        $query->orderByRaw("((avg_sales * {$multiplier}) - stok_ready) DESC");

        $writer = SimpleExcelWriter::streamDownload('saran_order_'.date('Y-m-d_H-i').'.xlsx');

        $query->cursor()->each(function ($row) use ($writer, $multiplier) {
            $saranOrder = ($row->avg_sales * $multiplier) - $row->stok_ready;
            $saranOrder = max(0, round($saranOrder));

            $writer->addRow([
                'Perusahaan'   => $row->unit?->nama,
                'Bidang'       => $row->type?->nama,
                'Kategori'     => $row->kategori?->nama,
                'Nama Barang'  => $row->barang_nama?->nama,
                'Kode'         => $row->kode,
                'Merk'         => $row->merk,
                'Jenis'        => $row->jenis == 1 ? 'PPN' : ($row->jenis == 2 ? 'Non PPN' : '-'),
                'Stok Saat Ini'=> (float) $row->stok_ready,
                'Satuan'       => $row->satuan?->nama,
                'Rata2 Jual'   => round($row->avg_sales),
                'Saran Order'  => $saranOrder,
            ]);

            if (ob_get_length() > 0) {
                ob_flush();
                flush();
            }
        });

        exit;
    }
}
