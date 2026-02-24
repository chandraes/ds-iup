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
use App\Models\ReturSupplier;
use App\Models\StokRetur;
use App\Models\transaksi\InvoiceJual;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Illuminate\Support\LazyCollection;
use App\Models\transaksi\InvoiceJualDetail;
use App\Models\Wilayah;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
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
        $selectUnit = BarangUnit::select('id', 'nama')->where('id', Auth::user()->barang_unit_id)->get();
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

        $query->where('barangs.barang_unit_id', Auth::user()->barang_unit_id);

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
        // $request->validate([
        //     'unit' => 'required|exists:barang_units,id'
        // ], [
        //     'unit.required' => ' Silahkan Melakukan Filter Perusahaan Terlebih Dahulu!!',
        //     'unit.exists' => 'Perusahaan yang dipilih tidak valid'
        // ]);
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

        $query->where('barangs.barang_unit_id', Auth::user()->barang_unit_id);

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

        $perusahaan = BarangUnit::find(Auth::user()->barang_unit_id);
        // Load View PDF
        $pdf = Pdf::loadView('db.order.pdf', compact('data', 'multiplier', 'perusahaan'));

        // Set Paper Size (Optional)
        $pdf->setPaper('a4', 'landscape');

        return $pdf->stream('laporan_saran_order.pdf');
    }

    // Tambahkan method ini di dalam class DatabaseController
    public function export_excel(Request $request)
    {
        // $request->validate([
        //     'unit' => 'required|exists:barang_units,id'
        // ], [
        //     'unit.required' => ' Silahkan Melakukan Filter Perusahaan Terlebih Dahulu!!',
        //     'unit.exists' => 'Perusahaan yang dipilih tidak valid'
        // ]);
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

        $query->where('barangs.barang_unit_id', Auth::user()->barang_unit_id);

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

    public function stok_retur_data(Request $request)
    {
        if ($request->ajax()) {
            $userId = Auth::user()->barang_unit_id;

            // Query Utama dengan Left Join ke Keranjang User
            // Tujuannya agar kita tahu row mana yang sedang ada di keranjang user tersebut
            $query = StokRetur::with(['barang.unit', 'barang.kategori', 'barang.satuan', 'barang.barang_nama'])
                    // ->leftJoin('stok_retur_carts', function($join) use ($userId) {
                    //     $join->on('stok_returs.id', '=', 'stok_retur_carts.stok_retur_id')
                    //         ->where('stok_retur_carts.user_id', '=', $userId);
                    // })
                    ->where('stok_returs.total_qty_karantina', '>', 0)
                    ->select(
                        'stok_returs.*'
                    );

            // --- Logic Filter (Tetap sama) ---
            // if ($request->has('unit_filter') && $request->unit_filter != '') {
            //     $query->whereHas('barang', function($q) use ($request) {
            //         $q->where('barang_unit_id', $request->unit_filter);
            //     });
            // }

            $query->whereHas('barang', function($q) use ($userId) {
                    $q->where('barang_unit_id', $userId);
                });

            if ($request->has('kategori_filter') && $request->kategori_filter != '') {
                $query->whereHas('barang', function($q) use ($request) {
                    $q->where('barang_kategori_id', $request->kategori_filter);
                });
            }

            return datatables()->of($query)
                ->addIndexColumn()
                // ... (Kolom perusahaan s/d non_ppn tetap sama) ...
                ->addColumn('perusahaan', function($row){ return $row->barang->unit->nama ?? '-'; })
                ->addColumn('kelompok', function($row){ return $row->barang->kategori->nama ?? '-'; })
                ->addColumn('nama_barang', function($row){ return $row->barang->barang_nama->nama ?? '-'; })
                ->addColumn('kode_barang', function($row){ return $row->barang->kode ?? '-'; })
                ->addColumn('merk', function($row){ return $row->barang->merk ?? '-'; })
                ->addColumn('stok_retur', function($row){
                    return '<span class="fw-bold text-danger">'.number_format($row->total_qty_karantina).'</span>';
                })
                ->addColumn('satuan', function($row){ return $row->barang->satuan->nama ?? '-'; })
                ->addColumn('ppn', function($row){ return ($row->barang->jenis == 1) ? '<span class="badge bg-success">Ya</span>' : '-'; })
                ->addColumn('non_ppn', function($row){ return ($row->barang->jenis == 2) ? '<span class="badge bg-success">Ya</span>' : '-'; })
                ->addColumn('detail_sumber', function($row){
                    return '<button type="button" class="btn btn-sm btn-info text-white btn-history"
                            data-id="'.$row->id.'" data-nama="'.$row->barang->barang_nama->nama.'">
                            <i class="bi bi-clock-history"></i> Lihat</button>';
                })
                // --- MODIFIKASI KOLOM AKSI ---

                ->rawColumns(['stok_retur', 'ppn', 'non_ppn', 'detail_sumber'])
                ->make(true);
        }
    }

    public function stok_retur(Request $request)
    {
        $unitId = Auth::user()->barang_unit_id;

       $units = BarangUnit::where('id', $unitId)->get();
        // $kategoris = BarangKategori::all();
         $kategoris = BarangKategori::whereHas('barangs', function($q) use ($unitId) {
                $q->where('is_active', 1); // Opsional: hanya yang aktif
                $q->where('barang_unit_id', $unitId);
            })->get();
        // 5. Kirim data dan nilai filter ke view
        return view('perusahaan.barang-retur-kirim.index', [
            'units' => $units,
            'kategoris' => $kategoris
        ]);
    }

    public function stok_retur_sumber($id)
    {
        $stokRetur = StokRetur::with([
            'barang.barang_nama',
            'barang.satuan',
            // Load sampai ke konsumen dan barang stok harga (batch asal)
            'sources.detail.barang_retur.konsumen.kode_toko',
            'sources.detail.stok'
        ])->findOrFail($id);

        // Kita return berupa Partial View (HTML potongan)
        return view('perusahaan.barang-retur-kirim.partials.history', compact('stokRetur'));
    }

    public function invoiceIndex()
    {
        $units = BarangUnit::where('id', Auth::user()->barang_unit_id)->get(); // Untuk filter
        return view('perusahaan.barang-retur-kirim.invoice-index', compact('units'));
    }

   public function invoiceShow($id)
    {
        // [UBAH DISINI] Load relasi 'barang_unit'
        $invoice = ReturSupplier::with(['barang_unit', 'user', 'details.barang.barang_nama', 'details.barang.satuan'])->where('barang_unit_id', Auth::user()->barang_unit_id)->findOrFail($id);
        return view('perusahaan.barang-retur-kirim.partials.invoice-detail', compact('invoice'));
    }

    public function invoiceData(Request $request)
    {
        if ($request->ajax()) {
            // [UBAH DISINI] Ganti 'unit' menjadi 'barang_unit'
            $query = ReturSupplier::with(['barang_unit', 'user'])
                    ->withCount('details');

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('created_at', [
                    $request->start_date . ' 00:00:00',
                    $request->end_date . ' 23:59:59'
                ]);
            }

            $query->where('barang_unit_id', Auth::user()->barang_unit_id);


            return datatables()->of($query)
                ->addIndexColumn()
                ->addColumn('nomor_display', function($row){
                    return '<span class="fw-bold font-monospace text-primary">RS-' . sprintf('%04d', $row->nomor) . '</span>';
                })
                ->editColumn('created_at', function($row){
                    return $row->created_at->format('Y-m-d');
                })
                // [UBAH DISINI] Akses relasi 'barang_unit'
                ->addColumn('supplier', function($row){
                    return $row->barang_unit->nama ?? '-';
                })
               // --- LOGIKA KOLOM 1: TAHAP PROSES (Packing) ---
                ->addColumn('status_proses', function($row){
                    // Jika 0: Masih disini (Kuning)
                    // Jika > 0: Sudah lewat (Centang Hijau)
                    if ($row->tipe == 0) {
                        return '<span class="badge bg-warning text-dark"><i class="bi bi-box-seam"></i> Diproses</span>';
                    } elseif ($row->tipe > 0 && $row->tipe != 99) {
                        return '<span class="text-success"><i class="bi bi-check-circle-fill fs-5"></i></span>';
                    } else {
                        return '<span class="text-muted">-</span>'; // Void
                    }
                })

                // --- LOGIKA KOLOM 2: TAHAP PENGIRIMAN ---
                ->addColumn('status_kirim', function($row){
                    // Jika 0: Belum sampai sini (Abu-abu)
                    // Jika 1: Sedang disini (Biru)
                    // Jika 2: Selesai (Centang Hijau)
                    if ($row->tipe == 0) {
                        return '<span class="text-muted opacity-25"><i class="bi bi-dash-lg"></i></span>';
                    } elseif ($row->tipe == 1) {
                        return '<span class="badge bg-info text-dark"><i class="bi bi-truck"></i> Jalan</span>';
                    } elseif ($row->tipe == 2) {
                        return '<span class="badge bg-success"><i class="bi bi-check-all"></i> Diterima</span>';
                    } else {
                        return '<span class="badge bg-danger">Void</span>';
                    }
                })
                ->addColumn('total_item', function($row){
                    return '<span class="badge bg-light text-dark border">' . $row->details_count . ' Item</span>';
                })
                ->rawColumns(['nomor_display', 'status_proses', 'status_kirim', 'total_item'])
                ->make(true);
        }

    }

    public function omset_tahunan_konsumen(Request $request)
    {
        // Cek apakah request dari DataTables (AJAX)
        if ($request->ajax()) {
            $tahun = $request->input('tahun', date('Y'));
            $unitId = Auth::user()->barang_unit_id;

            $kodeTokoId = $request->input('kode_toko_id'); // <--- Tangkap input baru
            $statusOmset = $request->input('status_omset');
            $salesId = $request->input('sales_id');
            $kabupatenKotaId = $request->input('kabupaten_kota_id');
            $kecamatanId = $request->input('kecamatan_id');
            $statusInvoice = $request->input('status_invoice');

            $bulan = $request->input('bulan', []); // Tangkap filter bulan (array)

            $db = new InvoiceJual;

            $query = $db->getOmsetQuery($tahun, $unitId, $kodeTokoId, $statusOmset, $salesId, $kabupatenKotaId, $kecamatanId, $statusInvoice, $bulan);

            $sql = $query->toSql();
            $bindings = $query->getBindings();

            $grandTotals = DB::table(DB::raw("($sql) as temp_total"))
                ->setBindings($bindings)
                ->selectRaw('
                    COALESCE(SUM(bulan_1), 0) as sum_b1,
                    COALESCE(SUM(bulan_2), 0) as sum_b2,
                    COALESCE(SUM(bulan_3), 0) as sum_b3,
                    COALESCE(SUM(bulan_4), 0) as sum_b4,
                    COALESCE(SUM(bulan_5), 0) as sum_b5,
                    COALESCE(SUM(bulan_6), 0) as sum_b6,
                    COALESCE(SUM(bulan_7), 0) as sum_b7,
                    COALESCE(SUM(bulan_8), 0) as sum_b8,
                    COALESCE(SUM(bulan_9), 0) as sum_b9,
                    COALESCE(SUM(bulan_10), 0) as sum_b10,
                    COALESCE(SUM(bulan_11), 0) as sum_b11,
                    COALESCE(SUM(bulan_12), 0) as sum_b12,
                    COALESCE(SUM(total_setahun), 0) as sum_total
                ')->first();
            // Opsi: Jika difilter unit, hanya tampilkan konsumen yg pernah beli unit itu
            // if ($unitId) {
            //     $query->whereNotNull('transaksi.konsumen_id');
            // }

            // ------------------------------------------------------------------
            // LANGKAH 4: Return ke DataTables
            // ------------------------------------------------------------------
            return DataTables::of($query)
                ->with('grand_totals', $grandTotals)
                ->addIndexColumn()
                ->addColumn('kode_toko', function($row){
                    return $row->kode_toko->kode ?? $row->kode_toko_id ?? '-';
                })
                // Format angka menjadi Rupiah untuk tampilan
                ->editColumn('kabupaten_kota.nama_wilayah', function($row) {
                    $nama = $row->kabupaten_kota?->nama_wilayah ?? '-';
                    return str_replace(['Kab. ', 'Kota '], '', $nama);
                })
                ->editColumn('kecamatan.nama_wilayah', function($row) {
                    $nama = $row->kecamatan?->nama_wilayah ?? '-';
                    return str_replace(['Kec. '], '', $nama);
                })
                ->editColumn('bulan_1', fn($row) => number_format($row->bulan_1 ?? 0, 0, ',', '.'))
                ->editColumn('bulan_2', fn($row) => number_format($row->bulan_2 ?? 0, 0, ',', '.'))
                ->editColumn('bulan_3', fn($row) => number_format($row->bulan_3 ?? 0, 0, ',', '.'))
                ->editColumn('bulan_4', fn($row) => number_format($row->bulan_4 ?? 0, 0, ',', '.'))
                ->editColumn('bulan_5', fn($row) => number_format($row->bulan_5 ?? 0, 0, ',', '.'))
                ->editColumn('bulan_6', fn($row) => number_format($row->bulan_6 ?? 0, 0, ',', '.'))
                ->editColumn('bulan_7', fn($row) => number_format($row->bulan_7 ?? 0, 0, ',', '.'))
                ->editColumn('bulan_8', fn($row) => number_format($row->bulan_8 ?? 0, 0, ',', '.'))
                ->editColumn('bulan_9', fn($row) => number_format($row->bulan_9 ?? 0, 0, ',', '.'))
                ->editColumn('bulan_10', fn($row) => number_format($row->bulan_10 ?? 0, 0, ',', '.'))
                ->editColumn('bulan_11', fn($row) => number_format($row->bulan_11 ?? 0, 0, ',', '.'))
                ->editColumn('bulan_12', fn($row) => number_format($row->bulan_12 ?? 0, 0, ',', '.'))
                ->addColumn('total', function($row) {
                    return '<strong>'.number_format($row->total_setahun ?? 0, 0, ',', '.').'</strong>';
                })
                ->rawColumns(['total'])
                ->filterColumn('nama', function($q, $keyword) {
                    $q->where('konsumens.nama', 'like', "%{$keyword}%");
                })
                ->make(true);
        }

        // Jika bukan AJAX, tampilkan View awal
        $units = BarangUnit::select('id', 'nama')->where('id', Auth::user()->barang_unit_id)->get();
        $kodeTokos = KodeToko::select('id', 'kode')->get();
        $sales = Karyawan::with('jabatan')->whereHas('jabatan', function ($query) {
                    $query->where('is_sales', 1);
                })->select('id', 'nama')->get();
        $kabupatenKota = Wilayah::where('id_level_wilayah', 2)->get();
        // $kecamatan = Wilayah::where('id_level_wilayah', 3)->get();
        return view('perusahaan.omset-tahunan-konsumen.index', compact('units', 'kodeTokos', 'sales', 'kabupatenKota'));
    }

    public function print_omset(Request $request)
    {
        // Kita naikkan memory limit sedikit saja untuk query data
        ini_set('memory_limit', '256M');
        set_time_limit(300);

        $tahun = $request->input('tahun', date('Y'));
        $unitId = Auth::user()->barang_unit_id;
        $kodeTokoId = $request->input('kode_toko_id'); // <--- Tangkap input baru
        $statusOmset = $request->input('status_omset');
        $salesId = $request->input('sales_id');
        $kabupatenKotaId = $request->input('kabupaten_kota_id');
        $kecamatanId = $request->input('kecamatan_id');
        $statusInvoice = $request->input('status_invoice');

        $bulan = $request->input('bulan', []);
        $db = new InvoiceJual;
        // Ambil Data
        $laporan = $db->getOmsetQuery($tahun, $unitId, $kodeTokoId, $statusOmset, $salesId, $kabupatenKotaId, $kecamatanId, $statusInvoice, $bulan)
            ->orderBy('konsumens.kode', 'asc') // Sort default untuk cetak
            ->get();

        // Nama Unit untuk Judul
        $namaUnit = 'Semua Perusahaan';
        if ($unitId) {
            $unitDB = DB::table('barang_units')->where('id', $unitId)->first();
            $namaUnit = $unitDB->nama ?? '-';
        }

        return view('perusahaan.omset-tahunan-konsumen.print', compact('laporan', 'tahun', 'namaUnit', 'bulan'));
    }

    public function export_excel_omset(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        $unitId = Auth::user()->barang_unit_id;
        $kodeTokoId = $request->input('kode_toko_id');
        $statusOmset = $request->input('status_omset');
        $salesId = $request->input('sales_id');
        $kabupatenKotaId = $request->input('kabupaten_kota_id');
        $kecamatanId = $request->input('kecamatan_id');
        $statusInvoice = $request->input('status_invoice');

        // [BARU] Tangkap array bulan
        $bulan = $request->input('bulan', []);

        $fileName = 'Omset_Konsumen_' . $tahun . '.csv';

        $headers = [
            'Content-type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename=' . $fileName,
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        // [MODIFIKASI] Gunakan 'use ($bulan)' pada callback
        $callback = function() use ($tahun, $unitId, $kodeTokoId, $statusOmset, $salesId, $kabupatenKotaId, $kecamatanId, $statusInvoice, $bulan) {
            $file = fopen('php://output', 'w');

            // 1. Susun Header Excel Secara Dinamis
            $csvHeaders = [
                'No', 'Kode Konsumen', 'Kode Toko', 'Nama Toko','Kab/Kota','Kecamatan', 'Sales Area'
            ];

            $namaBulan = [
                1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
                7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
            ];

            // Looping header bulan (tambahkan hanya jika bulan tidak difilter atau termasuk yang dipilih)
            for ($i = 1; $i <= 12; $i++) {
                if (empty($bulan) || in_array((string)$i, $bulan)) {
                    $csvHeaders[] = $namaBulan[$i];
                }
            }
            $csvHeaders[] = 'Total'; // Tambahkan Total di akhir

            fputcsv($file, $csvHeaders);

            $db = new InvoiceJual;

            // 2. Ambil Query
            $query = $db->getOmsetQuery($tahun, $unitId, $kodeTokoId, $statusOmset, $salesId, $kabupatenKotaId, $kecamatanId, $statusInvoice, $bulan)
                ->orderBy('konsumens.kode', 'asc');

            // 3. CHUNKING
            $no = 1;
            $query->chunk(500, function($konsumens) use ($file, &$no, $bulan) { // [MODIFIKASI] use $bulan
                foreach ($konsumens as $row) {
                    // Siapkan baris data awal
                    $csvRow = [
                        $no++,
                        $row->full_kode,
                        $row->kode_toko->kode ?? $row->kode_toko_id ?? '-',
                        $row->nama,
                        str_replace(['Kab. ', 'Kota '], '', $row->kabupaten_kota?->nama_wilayah) ?? '-',
                        str_replace(['Kec. '], '', $row->kecamatan?->nama_wilayah) ?? '-',
                        $row->karyawan?->nama ?? '-'
                    ];

                    // Looping nilai per bulan secara dinamis
                    for ($i = 1; $i <= 12; $i++) {
                        if (empty($bulan) || in_array((string)$i, $bulan)) {
                            $kolomBulan = 'bulan_' . $i;
                            $csvRow[] = $row->$kolomBulan ?? 0;
                        }
                    }

                    $csvRow[] = $row->total_setahun ?? 0; // Masukkan total di akhir

                    fputcsv($file, $csvRow);
                }
                flush();
            });

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    public function detail_omset_page($konsumenId, $bulan, $tahun, Request $request)
    {
        $unitId = Auth::user()->barang_unit_id;
        $statusInvoice = $request->input('status_invoice');
        $bulanFilter = $request->input('bulanFilter'); // Format: "1,2,3" atau "total"

        // 1. Ambil Data Konsumen
        $konsumen = Konsumen::with('kode_toko')->where('id', $konsumenId)->select('nama', 'kode', 'kode_toko_id')->first();
        if (!$konsumen) abort(404, 'Konsumen tidak ditemukan');

        // 2. Query Detail Transaksi
        $query = DB::table('invoice_jual_details as d')
            ->join('invoice_juals as i', 'd.invoice_jual_id', '=', 'i.id')
            ->join('barangs as b', 'd.barang_id', '=', 'b.id')
            ->leftJoin('barang_namas as bn', 'b.barang_nama_id', '=', 'bn.id') // Join nama barang untuk info
            ->leftJoin('barang_units as u', 'b.barang_unit_id', '=', 'u.id') // Join unit untuk info
            ->select([
                'i.kode as no_invoice',
                'i.created_at as tanggal',
                'bn.nama as nama_barang',
                'b.kode as kode_barang',
                'u.nama as nama_unit',
                'd.jumlah',
                'd.harga_satuan',
                'd.diskon',
                'd.ppn',
                'd.total'
            ])
            ->where('i.konsumen_id', $konsumenId)
            ->where('i.void', 0) // Pastikan tidak mengambil nota void
            ->whereYear('i.created_at', $tahun);

        // Filter Bulan (Jika bukan 'total', filter per bulan)
       if ($bulan !== 'total') {
            $query->whereMonth('i.created_at', $bulan);

            // Fix: Gunakan createFromDate, paksa (int), dan set tanggal ke 1 agar aman
            $namaBulan = \Carbon\Carbon::createFromDate($tahun, (int)$bulan, 1)->isoFormat('MMMM');
        } else {
           // Jika klik pada kolom "Total" di ujung kanan
            if (!empty($bulanFilter)) {
                $arrayBulan = explode(',', $bulanFilter); // Ubah string "1,2" jadi array [1, 2]

                // Gunakan DB::raw untuk filter array bulan
                $query->whereIn(DB::raw('MONTH(i.created_at)'), $arrayBulan);

                // Menyusun nama bulan dinamis untuk judul (misal: "Januari, Maret, Desember")
                $namaBulanArray = [];
                foreach($arrayBulan as $b) {
                    $namaBulanArray[] = \Carbon\Carbon::createFromDate($tahun, (int)$b, 1)->isoFormat('MMMM');
                }
                $namaBulan = implode(', ', $namaBulanArray);
            } else {
                $namaBulan = "Setahun (Jan - Des)";
            }
        }

        // Filter Unit (Jika ada)
        if ($unitId) {
            $query->where('b.barang_unit_id', $unitId);
        }

        if ($statusInvoice) {
            if ($statusInvoice == 'invoice') {
                $query->where('i.lunas', 0);
            } elseif ($statusInvoice == 'lunas') {
                $query->where('i.lunas', 1);
            }
        }
        // Eksekusi Query
        $details = $query->orderBy('i.created_at', 'asc')->get();

        return view('perusahaan.omset-tahunan-konsumen.detail', compact(
            'details', 'konsumen', 'bulan', 'namaBulan', 'tahun', 'unitId'
        ));
    }
}
