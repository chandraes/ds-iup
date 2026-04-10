<?php

namespace App\Http\Controllers;

use App\Models\ChecklistKunjungan;
use App\Models\db\Barang\Barang;
use App\Models\db\Karyawan;
use App\Models\db\KodeToko;
use App\Models\db\Konsumen;
use App\Models\Katalog;
use App\Models\PasswordKonfirmasi;
use App\Models\ReturSupplier;
use App\Models\StokRetur;
use App\Models\Wilayah;
use App\Services\WaStatus;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if (Auth::user()->role == 'perusahaan') {
            $barang_ppn = Barang::where('barang_unit_id', Auth::user()->barang_unit_id)
                ->where('jenis', 1)
                ->first() ? 1 : 0;

            $barang_non_ppn = Barang::where('barang_unit_id', Auth::user()->barang_unit_id)
                ->where('jenis', 2)
                ->first() ? 1 : 0;

            $sr = StokRetur::whereHas('barang', function ($q) {
                $q->where('barang_unit_id', Auth::user()->barang_unit_id);
            })->where('status', 0)->count();

             $ps = ReturSupplier::whereNot('tipe', 99)->where('barang_unit_id', Auth::user()->barang_unit_id)->count();

            return view('home', [
                'barang_ppn' => $barang_ppn,
                'barang_non_ppn' => $barang_non_ppn,
                'sr' => $sr,
                'ps' => $ps,
            ]);
        }
        return view('home');
    }

    public function getStatusWa()
    {
        $service = new WaStatus;
        $result = $service->getStatusWa();

        return response()->json($result);
    }

    public function getKabKota(Request $request)
    {
        $provinsi = $request->provinsi;
        $db = new Wilayah;
        $provinsi_data = $db->find($provinsi);

        $data = $db->where('id_level_wilayah', 2)->where('id_induk_wilayah', $provinsi_data->id_wilayah)->get();

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);

    }

    public function getKecamatan(Request $request)
    {
        $kab = $request->kab;
        $db = new Wilayah;
        $kab_data = $db->find($kab);

        $data = $db->where('id_level_wilayah', 3)->where('id_induk_wilayah', $kab_data->id_wilayah)->get();

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    private function canEditChecklist($bulan, $tahun)
    {
        // Bypass untuk Super User
        if (Auth::user()->role === 'su') {
            return true;
        }

        $now = now();

        // Kondisi 1: Berada di bulan dan tahun yang sama dengan saat ini
        if ($bulan == $now->month && $tahun == $now->year) {
            return true;
        }

        // Kondisi 2: Berada di 1 bulan sebelumnya, TAPI tanggal saat ini <= 3
        $prevMonth = $now->copy()->subMonth();
        if ($bulan == $prevMonth->month && $tahun == $prevMonth->year && $now->day <= 3) {
            return true;
        }

        return false;
    }

    public function checklist_sales(Request $request)
    {
        $months = collect(range(1, 12))->mapWithKeys(function ($month) {
            return [$month => date('F', mktime(0, 0, 0, $month, 1))];
        })->toArray();

        $tahunFilter = $request->tahun ?? date('Y');
        $tahun_sekarang = date('Y');
        $pilihan_tahun = range($tahun_sekarang - 5, $tahun_sekarang + 1);

        if ($request->ajax()) {
            $filters = $request->only(['area', 'kecamatan', 'kode_toko', 'status']);

            if (Auth::user()->role == 'sales') {
                $filters['area'] = Auth::user()->karyawan_id;
            }

            // 1. Query Dasar Konsumen
            $baseQuery = Konsumen::select([
                    'konsumens.id',
                    'konsumens.kode_toko_id',
                    'konsumens.nama',
                    'konsumens.kecamatan_id',
                    'konsumens.karyawan_id',
                    'konsumens.kode',
                    'wilayahs.nama_wilayah',
                    'karyawans.nickname',
                    'kode_tokos.kode as kode_toko_str'
                ])
                ->leftJoin('wilayahs', 'konsumens.kecamatan_id', '=', 'wilayahs.id')
                ->leftJoin('karyawans', 'konsumens.karyawan_id', '=', 'karyawans.id')
                ->leftJoin('kode_tokos', 'konsumens.kode_toko_id', '=', 'kode_tokos.id')
                ->where('konsumens.active', 1)
                ->where('konsumens.checklist_kunjungan', 1) // Filter Konsumen yang memiliki checklist_kunjungan = true
                ->filter($filters);

            if ($request->filled('status_kunjungan')) {
                $statusFilter = $request->status_kunjungan;
                $currentMonth = date('n');
                $currentYear = date('Y');

               if ($statusFilter === 'visited' || $statusFilter === 'not_visited') {
                    $baseQuery->whereHas('checklists', function ($query) use ($currentMonth, $currentYear, $statusFilter) {
                        $query->where('bulan', $currentMonth)
                              ->where('tahun', $currentYear)
                              ->where('status', $statusFilter);
                    });
                } elseif ($statusFilter === 'empty') {
                    $baseQuery->whereDoesntHave('checklists', function ($query) use ($currentMonth, $currentYear) {
                        $query->where('bulan', $currentMonth)
                              ->where('tahun', $currentYear);
                    });
                }
            }

            $totalKonsumen = (clone $baseQuery)->count();
            $filteredIdsQuery = (clone $baseQuery)->select('konsumens.id');

            // 2. PERHITUNGAN STATISTIK DENGAN QUERY OPTIMAL
            $checklistsSummary = ChecklistKunjungan::whereIn('konsumen_id', $filteredIdsQuery)
                ->where('tahun', $tahunFilter)
                ->selectRaw('bulan, status, count(id) as total')
                ->groupBy('bulan', 'status')
                ->get();

            $totalVisited = 0;
            $totalNotVisited = 0;

            // Siapkan struktur array bulanan 1 - 12
            $monthlyData = [];
           for ($i = 1; $i <= 12; $i++) {
                $monthlyData[$i] = ['visited' => 0, 'not_visited' => 0, 'empty' => 0, 'percentage' => 0];
            }

            foreach ($checklistsSummary as $stat) {
                if ($stat->status === 'visited') {
                    $totalVisited += $stat->total;
                    $monthlyData[$stat->bulan]['visited'] = $stat->total;
                } else if ($stat->status === 'not_visited') {
                    $totalNotVisited += $stat->total;
                    $monthlyData[$stat->bulan]['not_visited'] = $stat->total;
                }
            }

            // Hitung Persentase Tahunan & Bulanan
            $targetTahunan = $totalKonsumen * 12;
            $persentaseTahunan = $targetTahunan > 0 ? round(($totalVisited / $targetTahunan) * 100, 2) : 0;

            $currentMonth = (int) date('n');
            $currentYear = (int) date('Y');
            $today = (int) date('j');

            foreach (range(1, 12) as $m) {
                $visited = $monthlyData[$m]['visited'];
                $notVisited = $monthlyData[$m]['not_visited'];

                // Belum dikunjungi = Total Toko yang tampil - (Dikunjungi + Tidak Dikunjungi)
                $monthlyData[$m]['empty'] = max(0, $totalKonsumen - ($visited + $notVisited));

                $monthlyData[$m]['percentage'] = $totalKonsumen > 0 ? round(($visited / $totalKonsumen) * 100, 2) : 0;

                $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $m, $tahunFilter);
                $totalHariKerja = 0;
                $hariKerjaTerlewati = 0;

                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $hari = date('w', mktime(0, 0, 0, $m, $d, $tahunFilter));

                    if ($hari != 0) { // Selain hari Minggu
                        $totalHariKerja++;

                        // Hitung hari kerja yang sudah terlewati
                        if ($tahunFilter < $currentYear || ($tahunFilter == $currentYear && $m < $currentMonth)) {
                            // Jika tahun/bulan sudah berlalu, semua hari kerja di bulan itu dianggap sudah terlewati
                            $hariKerjaTerlewati++;
                        } elseif ($tahunFilter == $currentYear && $m == $currentMonth) {
                            // Jika bulan berjalan, hitung sampai tanggal hari ini
                            if ($d <= $today) {
                                $hariKerjaTerlewati++;
                            }
                        }
                    }
                }

                $sisaHariKerja = max(0, $totalHariKerja - $hariKerjaTerlewati);
                $sisaTokoBelumDikunjungi = max(0, $totalKonsumen - $visited);

                // 1. Rata-rata wajib / Hari (Tanpa Koma)
                $monthlyData[$m]['avg_wajib'] = $totalHariKerja > 0 ? round($totalKonsumen / $totalHariKerja, 0) : 0;

                // 2. Rata-rata real / Hari (Tanpa Koma)
                $monthlyData[$m]['avg_real'] = $hariKerjaTerlewati > 0 ? round($visited / $hariKerjaTerlewati, 0) : 0;

                // 3. Target rata-rata sisa hari (Tanpa Koma)
                if ($tahunFilter < $currentYear || ($tahunFilter == $currentYear && $m < $currentMonth)) {
                    // Jika bulan/tahun sudah berlalu, target sisa hari sudah tidak relevan (bisa di set 0)
                    $monthlyData[$m]['avg_target'] = 0;
                } else {
                    $monthlyData[$m]['avg_target'] = $sisaHariKerja > 0 ? round($sisaTokoBelumDikunjungi / $sisaHariKerja, 0) : ($sisaTokoBelumDikunjungi > 0 ? $sisaTokoBelumDikunjungi : 0);
                }
            }

            // 3. Render Datatable
            $query = $baseQuery->with(['kecamatan', 'sales_area', 'kode_toko', 'karyawan', 'checklists' => function($q) use ($tahunFilter) {
                    $q->where('tahun', $tahunFilter);
                }]);

            $dataTable = DataTables::of($query)
                ->addColumn('full_kode', fn($row) => $row->full_kode)
                // Sekarang datanya langsung dari property SQL, bukan relasi Eloquent
                ->addColumn('nama_toko', fn($row) => $row->kode_toko_str . ' ' . $row->nama)
                ->addColumn('nama_kecamatan', fn($row) => str_replace('Kec. ', '', $row->nama_wilayah))
                ->addColumn('sales_area', fn($row) => $row->nickname);

            foreach (range(1, 12) as $m) {
                $dataTable->addColumn('bulan_' . $m, function ($row) use ($m, $tahunFilter) {
                    $checklist = $row->checklists->firstWhere('bulan', $m);
                    return [
                        'konsumen_id' => $row->id,
                        'bulan' => $m,
                        'tahun' => $tahunFilter,
                        'status' => $checklist ? $checklist->status : 'empty',
                        'can_edit' => $this->canEditChecklist($m, $tahunFilter)
                    ];
                });
            }


            return $dataTable->with([
                'summary' => [
                    'total_konsumen' => number_format($totalKonsumen, 0, ',', '.'),
                    'total_visited' => number_format($totalVisited, 0, ',', '.'),
                    'total_not_visited' => number_format($totalNotVisited, 0, ',', '.'),
                    'persentase_tahun' => $persentaseTahunan,
                    'monthly' => $monthlyData,
                ]
            ])->make(true);
        }
        $kecamatanAreaSales = Auth::user()->role == 'sales' ? Konsumen::where('karyawan_id', Auth::user()->karyawan_id)->pluck('kecamatan_id')->unique() : [];

        $kecamatan_filter = Wilayah::when(Auth::user()->role == 'sales', function ($q) use ($kecamatanAreaSales) {
                    $q->whereIn('id', $kecamatanAreaSales);
                })->get();

        // dd($kecamatanAreaSales);
        // TAMPILAN AWAL HALAMAN (Load master data untuk select2)
        // $kecamatan_filter = Wilayah::whereIn('id_induk_wilayah', function ($query) {
        //     $query->select('id_wilayah')->from('wilayahs')->where('id_induk_wilayah', '110000');
        // })->where('id_level_wilayah', 3)->get();

        // $provinsi = Wilayah::where('id_level_wilayah', 1)->get();
        $sales_area = Karyawan::with('jabatan')->whereHas('jabatan', function ($query) {
            $query->where('is_sales', 1);
        })->select('id', 'nama')->get();

        return view('checklist-sales.index', [
            'kecamatan_filter' => $kecamatan_filter,
            'kode_toko' => KodeToko::select('id', 'kode')->get(),
            // 'provinsi' => $provinsi,
            'sales_area' => $sales_area,
            'months' => $months,
            'tahun_aktif' => $tahunFilter,
            'pilihan_tahun' => $pilihan_tahun
        ]);
    }

    public function update_checklist(Request $request)
    {
        $request->validate([
            'konsumen_id' => 'required',
            'bulan' => 'required|integer',
            'tahun' => 'required|integer',
            'status' => 'required|in:visited,not_visited'
        ]);

        // Proteksi ganda di sisi backend
        if (!$this->canEditChecklist($request->bulan, $request->tahun)) {
            return response()->json(['success' => false, 'message' => 'Batas waktu pengisian sudah habis.'], 403);
        }

        ChecklistKunjungan::updateOrCreate(
            [
                'konsumen_id' => $request->konsumen_id,
                'bulan' => $request->bulan,
                'tahun' => $request->tahun
            ],
            ['status' => $request->status]
        );

        return response()->json(['success' => true, 'message' => 'Status berhasil disimpan']);
    }

    public function uncheck_checklist(Request $request)
    {
        if (!in_array(Auth::user()->role, ['su', 'admin'])) {
            return response()->json(['message' => 'Akses ditolak. Tindakan ini hanya untuk Admin.'], 403);
        }

        $request->validate([
            'konsumen_id' => 'required',
            'bulan' => 'required',
            'tahun' => 'required',
            'password' => 'required'
        ]);

        // Proteksi ganda di sisi backend
        if (!$this->canEditChecklist($request->bulan, $request->tahun)) {
            return response()->json(['success' => false, 'message' => 'Batas waktu perubahan sudah habis.'], 403);
        }

        // Cek Password. Jika password Anda di database tidak di-hash, hilangkan Hash::check dan gunakan `where('password', $request->password)`
        $konfirmasi = PasswordKonfirmasi::first();
        if (!$konfirmasi || $konfirmasi->password !== $request->password) {
            // ATAU gunakan ini jika password menggunakan Hash:
            // if (!$konfirmasi || !Hash::check($request->password, $konfirmasi->password)) {
            return response()->json(['success' => false, 'message' => 'Password salah!'], 403);
        }

        ChecklistKunjungan::where('konsumen_id', $request->konsumen_id)
            ->where('bulan', $request->bulan)
            ->where('tahun', $request->tahun)
            ->delete();

        return response()->json(['success' => true, 'message' => 'Status berhasil dibatalkan']);
    }

    public function checklist_sales_download(Request $request)
    {
        if (!$request->filled('area')) {
            // Kembalikan ke halaman sebelumnya dengan pesan error
            return redirect()->back()->with('error', 'Gagal mengunduh: Anda wajib memilih Sales Area / Karyawan terlebih dahulu.');
        }

        ini_set('max_execution_time', 300); // Mengizinkan proses loading hingga 5 menit
        ini_set('memory_limit', '512M');   // Mengizinkan penggunaan RAM hingga 1GB (bisa dinaikkan jadi '2048M' jika datanya sangat besar)

       $tahunFilter = $request->tahun ?? date('Y');

        // 1. Query Dasar Konsumen
        $filters = $request->only(['area', 'kecamatan', 'kode_toko', 'status']);
        $baseQuery = Konsumen::select('id', 'kode_toko_id', 'nama', 'kecamatan_id', 'karyawan_id', 'kode')
            ->where('active', 1)
            ->filter($filters);

        if ($request->filled('status_kunjungan')) {
            $statusFilter = $request->status_kunjungan;
            $currentMonth = date('n');
            $currentYear = date('Y');

            if ($statusFilter === 'visited' || $statusFilter === 'not_visited') {
                $baseQuery->whereHas('checklists', function ($query) use ($currentMonth, $currentYear, $statusFilter) {
                    $query->where('bulan', $currentMonth)
                        ->where('tahun', $currentYear)
                        ->where('status', $statusFilter);
                });
            } elseif ($statusFilter === 'empty') {
                $baseQuery->whereDoesntHave('checklists', function ($query) use ($currentMonth, $currentYear) {
                    $query->where('bulan', $currentMonth)
                        ->where('tahun', $currentYear);
                });
            }
        }

        $totalKonsumen = (clone $baseQuery)->count();
        $filteredIdsQuery = (clone $baseQuery)->select('konsumens.id');

        // 2. PERHITUNGAN STATISTIK
        $checklistsSummary = ChecklistKunjungan::whereIn('konsumen_id', $filteredIdsQuery)
            ->where('tahun', $tahunFilter)
            ->selectRaw('bulan, status, count(id) as total')
            ->groupBy('bulan', 'status')
            ->get();

        $totalVisited = 0;
        $totalNotVisited = 0;
        $monthlyData = [];

        for ($i = 1; $i <= 12; $i++) {
            $monthlyData[$i] = ['visited' => 0, 'not_visited' => 0, 'empty' => 0, 'percentage' => 0];
        }

        foreach ($checklistsSummary as $stat) {
            if ($stat->status === 'visited') {
                $totalVisited += $stat->total;
                $monthlyData[$stat->bulan]['visited'] = $stat->total;
            } else if ($stat->status === 'not_visited') {
                $totalNotVisited += $stat->total;
                $monthlyData[$stat->bulan]['not_visited'] = $stat->total;
            }
        }

        $targetTahunan = $totalKonsumen * 12;
        $persentaseTahunan = $targetTahunan > 0 ? round(($totalVisited / $targetTahunan) * 100, 2) : 0;

        // 3. Ambil Data Lengkap (Bukan Datatable)
        $konsumens = $baseQuery->with(['kecamatan', 'karyawan', 'kode_toko', 'checklists' => function($q) use ($tahunFilter) {
                $q->where('tahun', $tahunFilter);
            }])
            ->orderBy('kecamatan_id', 'asc') // Urutkan sesuai kebutuhan
            ->get();

        // 4. Siapkan Data untuk View
        $data = [
            'title' => 'Laporan Checklist Sales - Tahun ' . $tahunFilter,
            'tahun' => $tahunFilter,
            'konsumens' => $konsumens,
            'total_konsumen' => $totalKonsumen,
            'total_visited' => $totalVisited,
            'total_not_visited' => $totalNotVisited,
            'persentase_tahun' => $persentaseTahunan,
            'monthly' => $monthlyData,
            'date_printed' => date('d-m-Y H:i')
        ];

        // 5. Generate PDF
        $pdf = Pdf::loadView('checklist-sales.pdf', $data);

        // Set kertas ke Legal (karena 12 kolom bulan butuh ruang lebar) dan Landscape
        $pdf->setPaper('legal', 'landscape');

        return $pdf->stream('Laporan_Checklist_Sales_'.$tahunFilter.'.pdf');
    }

    public function katalog(Request $request)
    {
        $filter = $request->only(['nama', 'slug']);

        $data = Katalog::filter($filter)->get();

        return view('katalog.index', [
            'data' => $data,
            'filter' => $filter,
        ]);
    }

    public function katalog_store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255|unique:katalogs,nama',
            'file' => 'nullable|file|mimes:pdf|max:2048', // Maksimal 2MB
        ]);

        $data['slug'] = Str::slug($data['nama']);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->storeAs('katalogs', $data['slug'] . '.pdf', 'public');
            $data['file'] = $path;
        } else {
            $data['file'] = null; // Atau bisa di-set ke string kosodng jika tidak ada file
        }

        Katalog::create($data);

        return redirect()->back()->with('success', 'Katalog berhasil ditambahkan.');
    }

    public function katalog_update(Katalog $katalog, Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255|unique:katalogs,nama,' . $katalog->id,
            'file' => 'nullable|file|mimes:pdf|max:2048', // Maksimal 2MB
        ]);

        $data['slug'] = Str::slug($data['nama']);

        if ($request->hasFile('file')) {
            // Hapus file lama jika ada
            if ($katalog->file) {
                Storage::disk('public')->delete($katalog->file);
            }
            $file = $request->file('file');
            $path = $file->storeAs('katalogs', $data['slug'] . '.pdf', 'public');
            $data['file'] = $path;
        } else {
            $data['file'] = $katalog->file; // Tetap gunakan file lama jika tidak ada file baru
        }

        $katalog->update($data);

        return redirect()->back()->with('success', 'Katalog berhasil diperbarui.');
    }

    public function katalog_destroy(Katalog $katalog)
    {
        // Hapus file dari storage
        if ($katalog->file) {
            Storage::disk('public')->delete($katalog->file);
        }

        // Hapus data katalog
        $katalog->delete();

        return redirect()->back()->with('success', 'Katalog berhasil dihapus.');
    }

    public function katalog_download(Katalog $katalog)
    {
        if (!$katalog->file) {
            return redirect()->back()->with('error', 'File katalog tidak ditemukan.');
        }

        $filePath = storage_path('app/public/' . $katalog->file);

        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'File katalog tidak ditemukan di server.');
        }

        return response()->download($filePath, $katalog->nama . '.pdf');
    }

}
