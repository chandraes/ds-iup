<?php

namespace App\Http\Controllers;

use App\Models\db\Barang\BarangUnit;
use App\Models\db\Karyawan;
use App\Models\db\Konsumen;
use App\Models\transaksi\InvoiceJual;
use Barryvdh\DomPDF\Facade\Pdf;
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

    // Function ini hanya bertugas menyiapkan Query Builder, tidak mengeksekusi ->get()
    private function getOmsetQuery($tahun, $unitId)
    {
        // 1. Subquery Transaksi (Sama seperti sebelumnya)
        $subquery = DB::table('invoice_juals as i')
            ->join('invoice_jual_details as d', 'i.id', '=', 'd.invoice_jual_id')
            ->join('barangs as b', 'd.barang_id', '=', 'b.id')
            ->select('i.konsumen_id')
            ->where('i.void', 0)
            ->whereYear('i.created_at', $tahun);

        if ($unitId) {
            $subquery->where('b.barang_unit_id', $unitId);
        }

        $selectsRaw = [];
        for ($m = 1; $m <= 12; $m++) {
            $selectsRaw[] = "COALESCE(SUM(CASE WHEN MONTH(i.created_at) = $m THEN d.total ELSE 0 END), 0) as bulan_$m";
        }
        $selectsRaw[] = "COALESCE(SUM(d.total), 0) as total_setahun";

        $subquery->addSelect(DB::raw(implode(',', $selectsRaw)));
        $subquery->groupBy('i.konsumen_id');

        // 2. Query Utama
        $query = Konsumen::query() // Pastikan namespace model benar
            ->select([
                'konsumens.id',
                'konsumens.kode',
                'konsumens.nama',
                'konsumens.kode_toko_id',
                // Ambil kolom hasil hitungan
                'transaksi.bulan_1', 'transaksi.bulan_2', 'transaksi.bulan_3',
                'transaksi.bulan_4', 'transaksi.bulan_5', 'transaksi.bulan_6',
                'transaksi.bulan_7', 'transaksi.bulan_8', 'transaksi.bulan_9',
                'transaksi.bulan_10', 'transaksi.bulan_11', 'transaksi.bulan_12',
                'transaksi.total_setahun'
            ])
            ->with('kode_toko')
            ->where('konsumens.active', 1)
            ->leftJoinSub($subquery, 'transaksi', function ($join) {
                $join->on('konsumens.id', '=', 'transaksi.konsumen_id');
            });

        return $query;
    }

    public function omset_tahunan_konsumen(Request $request)
    {
        // Cek apakah request dari DataTables (AJAX)
        if ($request->ajax()) {
            $tahun = $request->input('tahun', date('Y'));
            $unitId = $request->input('barang_unit_id');

            $query = $this->getOmsetQuery($tahun, $unitId);

            // Opsi: Jika difilter unit, hanya tampilkan konsumen yg pernah beli unit itu
            // if ($unitId) {
            //     $query->whereNotNull('transaksi.konsumen_id');
            // }

            // ------------------------------------------------------------------
            // LANGKAH 4: Return ke DataTables
            // ------------------------------------------------------------------
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('kode_toko', function($row){
                    return $row->kode_toko->kode ?? $row->kode_toko_id ?? '-';
                })
                // Format angka menjadi Rupiah untuk tampilan
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
        $units = BarangUnit::select('id', 'nama')->orderBy('nama')->get();

        return view('statistik.omset-tahunan-konsumen.index', compact('units'));
    }

    public function print_omset(Request $request)
    {
        // Kita naikkan memory limit sedikit saja untuk query data
        ini_set('memory_limit', '256M');
        set_time_limit(300);

        $tahun = $request->input('tahun', date('Y'));
        $unitId = $request->input('barang_unit_id');

        // Ambil Data
        $laporan = $this->getOmsetQuery($tahun, $unitId)
            ->orderBy('konsumens.kode', 'asc') // Sort default untuk cetak
            ->get();

        // Nama Unit untuk Judul
        $namaUnit = 'Semua Unit';
        if ($unitId) {
            $unitDB = DB::table('barang_units')->where('id', $unitId)->first();
            $namaUnit = $unitDB->nama ?? '-';
        }

        return view('statistik.omset-tahunan-konsumen.print', compact('laporan', 'tahun', 'namaUnit'));
    }

    public function export_excel_omset(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));
        $unitId = $request->input('barang_unit_id');

        // Nama File
        $fileName = 'Omset_Konsumen_' . $tahun . '.csv';

        // Header Kolom
        $headers = [
            'Content-type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename=' . $fileName,
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        // Fungsi Callback untuk Streaming
        $callback = function() use ($tahun, $unitId) {
            $file = fopen('php://output', 'w');

            // 1. Tulis Header Excel
            fputcsv($file, [
                'No', 'Kode Konsumen', 'Kode Toko', 'Nama Toko',
                'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
                'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des', 'Total'
            ]);

            // 2. Ambil Query (tanpa ->get() dulu)
            $query = $this->getOmsetQuery($tahun, $unitId);

            // 3. CHUNKING: Ambil data per 500 baris agar RAM aman
            $no = 1;
            $query->chunk(500, function($konsumens) use ($file, &$no) {
                foreach ($konsumens as $row) {
                    // Siapkan baris data
                    $csvRow = [
                        $no++,
                        $row->full_kode,
                        $row->kode_toko->kode ?? $row->kode_toko_id ?? '-', // Handle relation
                        $row->nama,
                        $row->bulan_1 ?? 0, // Biarkan angka asli (tanpa format Rp) agar bisa dihitung di Excel
                        $row->bulan_2 ?? 0,
                        $row->bulan_3 ?? 0,
                        $row->bulan_4 ?? 0,
                        $row->bulan_5 ?? 0,
                        $row->bulan_6 ?? 0,
                        $row->bulan_7 ?? 0,
                        $row->bulan_8 ?? 0,
                        $row->bulan_9 ?? 0,
                        $row->bulan_10 ?? 0,
                        $row->bulan_11 ?? 0,
                        $row->bulan_12 ?? 0,
                        $row->total_setahun ?? 0
                    ];
                    fputcsv($file, $csvRow);
                }
                // Flush buffer ke browser agar download terasa berjalan
                flush();
            });

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    public function detail_omset_page($konsumenId, $bulan, $tahun, Request $request)
    {
        $unitId = $request->input('unit_id');

        // 1. Ambil Data Konsumen
        $konsumen = Konsumen::where('id', $konsumenId)->select('nama', 'kode')->first();
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
            $namaBulan = "Setahun (Jan - Des)";
        }

        // Filter Unit (Jika ada)
        if ($unitId) {
            $query->where('b.barang_unit_id', $unitId);
        }

        // Eksekusi Query
        $details = $query->orderBy('i.created_at', 'asc')->get();

        return view('statistik.omset-tahunan-konsumen.detail', compact(
            'details', 'konsumen', 'bulan', 'namaBulan', 'tahun', 'unitId'
        ));
    }
}
