<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\db\Karyawan;
use App\Models\Transaksi;
use App\Models\transaksi\InvoiceJual;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
}
