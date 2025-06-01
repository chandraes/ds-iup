<?php

namespace App\Http\Controllers;

use App\Models\db\Barang\Barang;
use App\Models\db\Karyawan;
use App\Models\db\KodeToko;
use App\Models\db\Konsumen;
use App\Models\Wilayah;
use App\Services\WaStatus;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Storage;

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
        if (auth()->user()->role == 'perusahaan') {
            $barang_ppn = Barang::where('barang_unit_id', auth()->user()->barang_unit_id)
                ->where('jenis', 1)
                ->first() ? 1 : 0;

            $barang_non_ppn = Barang::where('barang_unit_id', auth()->user()->barang_unit_id)
                ->where('jenis', 2)
                ->first() ? 1 : 0;

            return view('home', [
                'barang_ppn' => $barang_ppn,
                'barang_non_ppn' => $barang_non_ppn,
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

    public function checklist_sales(Request $request)
    {
        $filters = $request->only(['area', 'kecamatan', 'kode_toko', 'status']); // Ambil filter dari request

        $data = Konsumen::select('id','kode_toko_id', 'nama', 'kecamatan_id', 'karyawan_id')
            ->filter($filters) // Gunakan scope filter
            ->with(['provinsi', 'kabupaten_kota', 'kecamatan', 'sales_area', 'kode_toko', 'karyawan'])
            // ->limit(10)
            ->get();

        // make array 1-12
        $months = collect(range(1, 12))->mapWithKeys(function ($month) {
            return [$month => date('F', mktime(0, 0, 0, $month, 1))];
        });

        $months = $months->toArray();

        // dd($months);

        $kecamatan_filter = Wilayah::whereIn('id_induk_wilayah', function ($query) {
            $query->select('id_wilayah')
                ->from('wilayahs')
                ->where('id_induk_wilayah', '110000');
        })->where('id_level_wilayah', 3)->get();

        $provinsi = Wilayah::where('id_level_wilayah', 1)->get();

        $sales_area = Karyawan::with('jabatan')->whereHas('jabatan', function ($query) {
            $query->where('is_sales', 1);
        })->select('id', 'nama')->get();

        return view('checklist-sales.index', [
            'data' => $data,
            'kecamatan_filter' => $kecamatan_filter,
            'kode_toko' => KodeToko::select('id', 'kode')->get(),
            'provinsi' => $provinsi,
            'sales_area' => $sales_area,
            'months' => $months,
        ]);
    }

    public function checklist_sales_download(Request $request)
    {

        ini_set('max_execution_time', 600); // Set waktu eksekusi maksimum
        ini_set('memory_limit', '512M'); // Set batas memori

        $filters = $request->only(['area', 'kecamatan', 'kode_toko', 'status']);
        $months = collect(range(1, 12))->mapWithKeys(fn($month) => [$month => date('F', mktime(0, 0, 0, $month, 1))])->toArray();

        // dd($filters);

        $chunkSize = 200;
        $pdfFiles = [];
        $chunkIndex = 1;
        $totalProcessed = 0;

        Konsumen::select('id','kode_toko_id', 'nama', 'kecamatan_id', 'karyawan_id')
                ->filter($filters)
                ->with(['provinsi', 'kabupaten_kota', 'kecamatan', 'sales_area', 'kode_toko', 'karyawan'])
                ->orderBy('kecamatan_id')
                ->chunk($chunkSize, function($dataChunk) use (&$pdfFiles, $months, &$chunkIndex, &$totalProcessed) {
                    $offset = $totalProcessed; // nomor awal untuk chunk ini
                    $pdf = Pdf::loadView('checklist-sales.pdf', [
                        'data' => $dataChunk,
                        'months' => $months,
                        'offset' => $offset
                    ])->setPaper('a4', 'portrait');

                    $filename = storage_path("app/public/checklist_sales_part" . ($chunkIndex + 1) . ".pdf");
                    $pdf->save($filename);
                    $pdfFiles[] = $filename;
                    $chunkIndex++;
                    $totalProcessed += count($dataChunk);
                });

            // Gabungkan semua PDF
        $finalPdf = new Fpdi();
        foreach ($pdfFiles as $file) {
            $pageCount = $finalPdf->setSourceFile($file);
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $tplIdx = $finalPdf->importPage($pageNo);
                $finalPdf->AddPage();
                $finalPdf->useTemplate($tplIdx);
            }
        }

        $finalPath = storage_path('app/public/checklist_sales_final.pdf');
        $finalPdf->Output($finalPath, 'F');

        // Hapus file chunk sementara
        foreach ($pdfFiles as $file) {
            @unlink($file);
        }

        // Download file hasil gabungan
        return response()->download($finalPath)->deleteFileAfterSend(true);
    }
}
