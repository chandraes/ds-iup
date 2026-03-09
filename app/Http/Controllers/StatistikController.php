<?php

namespace App\Http\Controllers;

use App\Models\db\Barang\BarangUnit;
use App\Models\db\Karyawan;
use App\Models\db\KodeToko;
use App\Models\db\Konsumen;
use App\Models\transaksi\InvoiceJual;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Yajra\DataTables\Facades\DataTables;

class StatistikController extends Controller
{
    public function index()
    {
        return view('statistik.index');
    }

    public function omset_harian_sales(Request $request)
    {
         $month = $request->input('month') ?? date('m');
        $year = $request->input('year') ?? date('Y');

        $db = new InvoiceJual();

        $dataTahun = $db->dataTahun();

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

        $data = $db->omset_harian($month, $year);

        return view('statistik.omset-harian.index', [
             'rows' => $data['data'],
            'karyawans' => $data['karyawans'],
            'dataTahun' => $dataTahun,
            'dataBulan' => $dataBulan,
        ]);
    }

    public function omset_harian_sales_detail(Request $request)
    {
        $req = $request->validate([
            'tanggal' => 'required|date',
            'karyawan_id' => 'required|exists:karyawans,id',
        ]);

        $db = new InvoiceJual;

        $karyawan = Karyawan::where('id',$req['karyawan_id'])->select('nama')->first();

        if ($karyawan == null) {
            return redirect()->back()->with('error', 'Karyawan tidak ditemukan');
        }

        $data = $db->omset_harian_detail($request->input('tanggal'), $request->input('karyawan_id'));

        return view('statistik.omset-harian.detail', [
            'data' => $data,
            'karyawan' => $karyawan,
        ]);
    }

    public function profit_harian(Request $request)
    {
        $month = $request->input('month') ?? date('m');
        $year = $request->input('year') ?? date('Y');

        $db = new InvoiceJual();

        $dataTahun = $db->dataTahun();

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

        $data = $db->profit_harian($month, $year);

        // dd($data);

        return view('statistik.profit.harian', [
            'data' => $data,
            'dataTahun' => $dataTahun,
            'dataBulan' => $dataBulan,
        ]);
    }

    public function omset_bulanan_konsumen(Request $request)
    {
        // Cek apakah request dari DataTables (AJAX)
        if ($request->ajax()) {
            $tahun = $request->input('tahun', date('Y'));
            $unitId = $request->input('barang_unit_id');

            $kodeTokoId = $request->input('kode_toko_id'); // <--- Tangkap input baru
            $statusOmset = $request->input('status_omset');
            $salesId = $request->input('sales_id');
            $kabupatenKotaId = $request->input('kabupaten_kota_id');
            $kecamatanId = $request->input('kecamatan_id');
            $statusInvoice = $request->input('status_invoice');
            $statusKonsumen = $request->input('status_konsumen'); // <--- Tangkap status konsumen

            $bulan = $request->input('bulan', []); // Tangkap filter bulan (array)

            $db = new InvoiceJual;

            $query = $db->getOmsetQuery($tahun, $unitId, $kodeTokoId, $statusOmset, $salesId, $kabupatenKotaId, $kecamatanId, $statusInvoice, $bulan, $statusKonsumen);

            // get unique konsumens.kabupaten_kota_id from query

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

        $kabupatenKotaIds = Konsumen::select('kabupaten_kota_id')->distinct()->pluck('kabupaten_kota_id')->filter()->toArray();
        // Jika bukan AJAX, tampilkan View awal
        $units = BarangUnit::select('id', 'nama')->orderBy('nama')->get();
        $kodeTokos = KodeToko::select('id', 'kode')->get();
        $sales = Karyawan::with('jabatan')->whereHas('jabatan', function ($query) {
                    $query->where('is_sales', 1);
                })->select('id', 'nama')->get();
        $kabupatenKota = Wilayah::where('id_level_wilayah', 2)->whereIn('id', $kabupatenKotaIds)->get();
        // $kecamatan = Wilayah::where('id_level_wilayah', 3)->get();
        return view('statistik.omset-bulanan-konsumen.index', compact('units', 'kodeTokos', 'sales', 'kabupatenKota'));
    }

    public function print_omset(Request $request)
    {
        // Kita naikkan memory limit sedikit saja untuk query data
        ini_set('memory_limit', '256M');
        set_time_limit(300);

        $tahun = $request->input('tahun', date('Y'));
        $unitId = $request->input('barang_unit_id');
        $kodeTokoId = $request->input('kode_toko_id'); // <--- Tangkap input baru
        $statusOmset = $request->input('status_omset');
        $salesId = $request->input('sales_id');
        $kabupatenKotaId = $request->input('kabupaten_kota_id');
        $kecamatanId = $request->input('kecamatan_id');
        $statusInvoice = $request->input('status_invoice');
        $statusKonsumen = $request->input('status_konsumen'); // <--- Tangkap status konsumen

        $bulan = $request->input('bulan', []);
        $db = new InvoiceJual;
        // Ambil Data
        $laporan = $db->getOmsetQuery($tahun, $unitId, $kodeTokoId, $statusOmset, $salesId, $kabupatenKotaId, $kecamatanId, $statusInvoice, $bulan, $statusKonsumen)
            ->orderBy('konsumens.kode', 'asc') // Sort default untuk cetak
            ->get();

        // Nama Unit untuk Judul
        $namaUnit = 'Semua Perusahaan';
        if ($unitId) {
            $unitDB = DB::table('barang_units')->where('id', $unitId)->first();
            $namaUnit = $unitDB->nama ?? '-';
        }

        return view('statistik.omset-bulanan-konsumen.print', compact('laporan', 'tahun', 'namaUnit', 'bulan'));
    }

    public function export_excel_omset(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        $unitId = $request->input('barang_unit_id');
        $kodeTokoId = $request->input('kode_toko_id');
        $statusOmset = $request->input('status_omset');
        $salesId = $request->input('sales_id');
        $kabupatenKotaId = $request->input('kabupaten_kota_id');
        $kecamatanId = $request->input('kecamatan_id');
        $statusInvoice = $request->input('status_invoice');
        $statusKonsumen = $request->input('status_konsumen'); // <--- Tangkap status konsumen

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
        $callback = function() use ($tahun, $unitId, $kodeTokoId, $statusOmset, $salesId, $kabupatenKotaId, $kecamatanId, $statusInvoice, $bulan, $statusKonsumen) {
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
            $query = $db->getOmsetQuery($tahun, $unitId, $kodeTokoId, $statusOmset, $salesId, $kabupatenKotaId, $kecamatanId, $statusInvoice, $bulan, $statusKonsumen)
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
        $unitId = $request->input('unit_id');
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

        return view('statistik.omset-bulanan-konsumen.detail', compact(
            'details', 'konsumen', 'bulan', 'namaBulan', 'tahun', 'unitId'
        ));
    }

    public function omset_tahunan_konsumen(Request $request)
    {
        if ($request->ajax()) {
            // Default tahun: 4 tahun kebelakang s/d tahun sekarang (5 tahun berjalan)
            $tahunAkhir = $request->input('tahun_akhir', date('Y'));
            $tahunAwal = $request->input('tahun_awal', date('Y') - 4);

            // Proteksi di request AJAX
            if (($tahunAkhir - $tahunAwal) > 4) {
                $tahunAwal = $tahunAkhir - 4;
            }

            $years = range($tahunAwal, $tahunAkhir); // Kita oper array ini ke Frontend

            $unitId = $request->input('barang_unit_id');
            $kodeTokoId = $request->input('kode_toko_id');
            $statusOmset = $request->input('status_omset');
            $salesId = $request->input('sales_id');
            $kabupatenKotaId = $request->input('kabupaten_kota_id');
            $kecamatanId = $request->input('kecamatan_id');
            $statusInvoice = $request->input('status_invoice');
            $statusKonsumen = $request->input('status_konsumen');

            $db = new InvoiceJual; // Sesuaikan dengan model Anda
            $query = $db->getOmsetTahunanQuery($tahunAwal, $tahunAkhir, $unitId, $kodeTokoId, $statusOmset, $salesId, $kabupatenKotaId, $kecamatanId, $statusInvoice, $statusKonsumen);

            $sql = $query->toSql();
            $bindings = $query->getBindings();

            $grandTotals = DB::table(DB::raw("($sql) as temp_total"))
                ->setBindings($bindings)
                ->selectRaw('
                    COALESCE(SUM(tahun_1), 0) as sum_t1,
                    COALESCE(SUM(tahun_2), 0) as sum_t2,
                    COALESCE(SUM(tahun_3), 0) as sum_t3,
                    COALESCE(SUM(tahun_4), 0) as sum_t4,
                    COALESCE(SUM(tahun_5), 0) as sum_t5,
                    COALESCE(SUM(total_semua), 0) as sum_total
                ')->first();

            return DataTables::of($query)
                ->with('grand_totals', $grandTotals)
                ->with('years_array', $years) // <--- PENTING: Untuk merubah Header di Datatables
                ->addIndexColumn()
                ->addColumn('kode_toko', function($row){
                    return $row->kode_toko->kode ?? $row->kode_toko_id ?? '-';
                })
                ->editColumn('kabupaten_kota.nama_wilayah', function($row) {
                    return str_replace(['Kab. ', 'Kota '], '', $row->kabupaten_kota?->nama_wilayah ?? '-');
                })
                ->editColumn('kecamatan.nama_wilayah', function($row) {
                    return str_replace(['Kec. '], '', $row->kecamatan?->nama_wilayah ?? '-');
                })
                ->editColumn('tahun_1', fn($row) => number_format($row->tahun_1 ?? 0, 0, ',', '.'))
                ->editColumn('tahun_2', fn($row) => number_format($row->tahun_2 ?? 0, 0, ',', '.'))
                ->editColumn('tahun_3', fn($row) => number_format($row->tahun_3 ?? 0, 0, ',', '.'))
                ->editColumn('tahun_4', fn($row) => number_format($row->tahun_4 ?? 0, 0, ',', '.'))
                ->editColumn('tahun_5', fn($row) => number_format($row->tahun_5 ?? 0, 0, ',', '.'))
                ->addColumn('total', function($row) {
                    return '<strong>'.number_format($row->total_semua ?? 0, 0, ',', '.').'</strong>';
                })
                ->rawColumns(['total'])
                ->make(true);
        }

        $kabupatenKotaIds = Konsumen::select('kabupaten_kota_id')->distinct()->pluck('kabupaten_kota_id')->filter()->toArray();
        $units = BarangUnit::select('id', 'nama')->orderBy('nama')->get();
        $kodeTokos = KodeToko::select('id', 'kode')->get();
        $sales = Karyawan::with('jabatan')->whereHas('jabatan', fn($q) => $q->where('is_sales', 1))->select('id', 'nama')->get();
        $kabupatenKota = Wilayah::where('id_level_wilayah', 2)->whereIn('id', $kabupatenKotaIds)->get();

        return view('statistik.omset-tahunan-konsumen.index', compact('units', 'kodeTokos', 'sales', 'kabupatenKota'));
    }

    public function detail_omset_tahunan_page($konsumenId, $tahunKlik, Request $request)
    {
        // Tangkap parameter filter yang dikirim dari halaman index
        $unitId = $request->input('unit_id');
        $statusInvoice = $request->input('status_invoice');
        $tahunAwal = $request->input('tahun_awal');
        $tahunAkhir = $request->input('tahun_akhir');

        // 1. Ambil Data Konsumen
        $konsumen = Konsumen::with('kode_toko')->where('id', $konsumenId)->select('nama', 'kode', 'kode_toko_id')->first();
        if (!$konsumen) abort(404, 'Konsumen tidak ditemukan');

        // 2. Query Detail Transaksi
        $query = DB::table('invoice_jual_details as d')
            ->join('invoice_juals as i', 'd.invoice_jual_id', '=', 'i.id')
            ->join('barangs as b', 'd.barang_id', '=', 'b.id')
            ->leftJoin('barang_namas as bn', 'b.barang_nama_id', '=', 'bn.id')
            ->leftJoin('barang_units as u', 'b.barang_unit_id', '=', 'u.id')
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
            ->where('i.void', 0); // Pastikan tidak mengambil nota void

        // --- FILTER TAHUN ---
        if ($tahunKlik !== 'total') {
            // Jika User klik pada kolom tahun spesifik (misal: 2023)
            $query->whereYear('i.created_at', $tahunKlik);
            $periodeTeks = "Tahun " . $tahunKlik;
        } else {
            // Jika User klik pada kolom "Total", tampilkan seluruh transaksi di range tahun filter
            if ($tahunAwal && $tahunAkhir) {
                $query->whereYear('i.created_at', '>=', $tahunAwal)
                    ->whereYear('i.created_at', '<=', $tahunAkhir);
                $periodeTeks = "Periode $tahunAwal - $tahunAkhir";
            } else {
                $periodeTeks = "Semua Tahun";
            }
        }

        // --- FILTER TAMBAHAN (Sama seperti index) ---
        if ($unitId) {
            $query->where('b.barang_unit_id', $unitId);
        }

        if ($statusInvoice) {
            if ($statusInvoice == 'invoice') {
                $query->where('i.lunas', 0);
            } elseif ($statusInvoice == 'piutang') { // Sesuaikan jika ada status piutang
                $query->where('i.status_bayar', '!=', 'lunas');
            }
        }

        // Eksekusi Query
        $details = $query->orderBy('i.created_at', 'asc')->get();

        return view('statistik.omset-tahunan-konsumen.detail', compact(
            'details', 'konsumen', 'tahunKlik', 'periodeTeks', 'unitId', 'tahunAwal', 'tahunAkhir'
        ));
    }

    public function print_omset_tahunan(Request $request)
    {
        ini_set('memory_limit', '256M');
        set_time_limit(300);

        // Filter Tahun
        $tahunAkhir = $request->input('tahun_akhir', date('Y'));
        $tahunAwal = $request->input('tahun_awal', date('Y') - 4);

        if (($tahunAkhir - $tahunAwal) > 4) {
            $tahunAwal = $tahunAkhir - 4;
        }
        $years = range($tahunAwal, $tahunAkhir); // Array tahun untuk header dinamis

        // Tangkap Filter Lainnya
        $unitId = $request->input('barang_unit_id');
        $kodeTokoId = $request->input('kode_toko_id');
        $statusOmset = $request->input('status_omset');
        $salesId = $request->input('sales_id');
        $kabupatenKotaId = $request->input('kabupaten_kota_id');
        $kecamatanId = $request->input('kecamatan_id');
        $statusInvoice = $request->input('status_invoice');
        $statusKonsumen = $request->input('status_konsumen');

        $db = new InvoiceJual;

        // Ambil Data dari Query Tahunan
        $laporan = $db->getOmsetTahunanQuery($tahunAwal, $tahunAkhir, $unitId, $kodeTokoId, $statusOmset, $salesId, $kabupatenKotaId, $kecamatanId, $statusInvoice, $statusKonsumen)
            ->orderBy('konsumens.kode', 'asc')
            ->get();

        // Nama Unit untuk Judul
        $namaUnit = 'Semua Perusahaan';
        if ($unitId) {
            $unitDB = DB::table('barang_units')->where('id', $unitId)->first();
            $namaUnit = $unitDB->nama ?? '-';
        }

        return view('statistik.omset-tahunan-konsumen.print', compact('laporan', 'tahunAwal', 'tahunAkhir', 'years', 'namaUnit'));
    }

    public function export_excel_omset_tahunan(Request $request)
    {
        // Filter Tahun
        $tahunAkhir = $request->input('tahun_akhir', date('Y'));
        $tahunAwal = $request->input('tahun_awal', date('Y') - 4);

        if (($tahunAkhir - $tahunAwal) > 4) {
            $tahunAwal = $tahunAkhir - 4;
        }
        $years = range($tahunAwal, $tahunAkhir); // Array tahun untuk header dinamis

        // Tangkap Filter Lainnya
        $unitId = $request->input('barang_unit_id');
        $kodeTokoId = $request->input('kode_toko_id');
        $statusOmset = $request->input('status_omset');
        $salesId = $request->input('sales_id');
        $kabupatenKotaId = $request->input('kabupaten_kota_id');
        $kecamatanId = $request->input('kecamatan_id');
        $statusInvoice = $request->input('status_invoice');
        $statusKonsumen = $request->input('status_konsumen');

        $fileName = 'Omset_Tahunan_Konsumen_' . $tahunAwal . '_' . $tahunAkhir . '.csv';

        $headers = [
            'Content-type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename=' . $fileName,
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function() use ($tahunAwal, $tahunAkhir, $years, $unitId, $kodeTokoId, $statusOmset, $salesId, $kabupatenKotaId, $kecamatanId, $statusInvoice, $statusKonsumen) {
            $file = fopen('php://output', 'w');

            // 1. Susun Header Excel Secara Dinamis
            $csvHeaders = [
                'No', 'Kode Konsumen', 'Kode Toko', 'Nama Toko', 'Kab/Kota', 'Kecamatan', 'Sales Area'
            ];

            // Tambahkan Header Tahun (misal: 2022, 2023, 2024)
            foreach ($years as $year) {
                $csvHeaders[] = $year;
            }
            $csvHeaders[] = 'Total'; // Kolom Grand Total

            fputcsv($file, $csvHeaders);

            $db = new InvoiceJual;

            // 2. Ambil Query
            $query = $db->getOmsetTahunanQuery($tahunAwal, $tahunAkhir, $unitId, $kodeTokoId, $statusOmset, $salesId, $kabupatenKotaId, $kecamatanId, $statusInvoice, $statusKonsumen)
                ->orderBy('konsumens.kode', 'asc');

            // 3. CHUNKING
            $no = 1;
            $query->chunk(500, function($konsumens) use ($file, &$no, $years) {
                foreach ($konsumens as $row) {
                    $csvRow = [
                        $no++,
                        $row->full_kode, // Jika null coba ganti ke $row->kode sesuai setup Anda
                        $row->kode_toko->kode ?? $row->kode_toko_id ?? '-',
                        $row->nama,
                        str_replace(['Kab. ', 'Kota '], '', $row->kabupaten_kota?->nama_wilayah) ?? '-',
                        str_replace(['Kec. '], '', $row->kecamatan?->nama_wilayah) ?? '-',
                        $row->karyawan?->nama ?? '-'
                    ];

                    // Looping nilai per tahun (tahun_1 s/d tahun_N)
                    // Jumlah kolom tahun akan sama dengan jumlah elemen di array $years
                    for ($i = 1; $i <= count($years); $i++) {
                        $kolomTahun = 'tahun_' . $i;
                        $csvRow[] = $row->$kolomTahun ?? 0;
                    }

                    $csvRow[] = $row->total_semua ?? 0; // Masukkan total semua di akhir

                    fputcsv($file, $csvRow);
                }
                flush();
            });

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}
