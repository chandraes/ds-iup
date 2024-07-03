<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\Customer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StatistikController extends Controller
{
    public function index(Request $request)
    {
        $customer = Customer::findOrFail($request->customer);

        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');

        $db = new Transaksi;

        $dataTahun = $db->dataTahun();

        $nama_bulan = Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;
        $date = Carbon::createFromDate($tahun, $bulan)->daysInMonth;

        $bulanan = $db->statistikBulanan($customer->id, $bulan, $tahun);
        $tahunan = $db->statistikTahunan($customer->id, $tahun);


        $statistics_monthly = [];
        $statistics_yearly = [];

        for ($i = 1; $i <= $date; $i++) {
            $statistics_monthly[$i] = [
                'total_berat' => 0,
                'total_bayar' => 0,
                'total_tagihan' => 0,
                'total_profit' => 0,
            ];
        }

        for ($i = 1; $i <= 12; $i++) {
            $statistics_yearly[$i] = [
                'total_berat' => 0,
                'total_bayar' => 0,
                'total_tagihan' => 0,
                'total_profit' => 0,
            ];
        }

        $yearly_total_berat = 0;
        $yearly_total_bayar = 0;
        $yearly_total_tagihan = 0;
        $yearly_total_profit = 0;


        foreach ($tahunan as $data) {
            $month = date('n', strtotime($data->tanggal)); // get the month of the year

            $statistics_yearly[$month]['total_berat'] += $data->berat;
            $statistics_yearly[$month]['total_bayar'] += $data->total_bayar;
            $statistics_yearly[$month]['total_tagihan'] += $data->total_tagihan;
            $statistics_yearly[$month]['total_profit'] += $data->profit;

            $yearly_total_berat += $data->berat;
            $yearly_total_bayar += $data->total_bayar;
            $yearly_total_tagihan += $data->total_tagihan;
            $yearly_total_profit += $data->profit;

        }

        $grand_total_berat = 0;
        $grand_total_bayar = 0;
        $grand_total_tagihan = 0;
        $grand_total_profit = 0;

        foreach ($bulanan as $data) {
            $day = date('j', strtotime($data->tanggal)); // get the day of the month

            $statistics_monthly[$day]['total_berat'] += $data->berat;
            $statistics_monthly[$day]['total_bayar'] += $data->total_bayar;
            $statistics_monthly[$day]['total_tagihan'] += $data->total_tagihan;
            $statistics_monthly[$day]['total_profit'] += $data->profit;

            $grand_total_berat += $data->berat;
            $grand_total_bayar += $data->total_bayar;
            $grand_total_tagihan += $data->total_tagihan;
            $grand_total_profit += $data->profit;

        }

        return view('rekap.statistik.index', [
            'customer' => $customer,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'dataTahun' => $dataTahun,
            'nama_bulan' => $nama_bulan,
            'statistics_monthly' => $statistics_monthly,
            'statistics_yearly' => $statistics_yearly,
            'grand_total_berat' => $grand_total_berat,
            'grand_total_bayar' => $grand_total_bayar,
            'grand_total_tagihan' => $grand_total_tagihan,
            'grand_total_profit' => $grand_total_profit,
            'yearly_total_berat' => $yearly_total_berat,
            'yearly_total_bayar' => $yearly_total_bayar,
            'yearly_total_tagihan' => $yearly_total_tagihan,
            'yearly_total_profit' => $yearly_total_profit,
        ]);

    }

    public function print(Request $request)
    {
        $customer = Customer::findOrFail($request->customer);

        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');

        $db = new Transaksi;

        $dataTahun = $db->dataTahun();

        $nama_bulan = Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;
        $date = Carbon::createFromDate($tahun, $bulan)->daysInMonth;

        $bulanan = $db->statistikBulanan($customer->id, $bulan, $tahun);
        $tahunan = $db->statistikTahunan($customer->id, $tahun);


        $statistics_monthly = [];
        $statistics_yearly = [];

        for ($i = 1; $i <= $date; $i++) {
            $statistics_monthly[$i] = [
                'total_berat' => 0,
                'total_bayar' => 0,
                'total_tagihan' => 0,
                'total_profit' => 0,
            ];
        }

        for ($i = 1; $i <= 12; $i++) {
            $statistics_yearly[$i] = [
                'total_berat' => 0,
                'total_bayar' => 0,
                'total_tagihan' => 0,
                'total_profit' => 0,
            ];
        }

        $yearly_total_berat = 0;
        $yearly_total_bayar = 0;
        $yearly_total_tagihan = 0;
        $yearly_total_profit = 0;


        foreach ($tahunan as $data) {
            $month = date('n', strtotime($data->tanggal)); // get the month of the year

            $statistics_yearly[$month]['total_berat'] += $data->berat;
            $statistics_yearly[$month]['total_bayar'] += $data->total_bayar;
            $statistics_yearly[$month]['total_tagihan'] += $data->total_tagihan;
            $statistics_yearly[$month]['total_profit'] += $data->profit;

            $yearly_total_berat += $data->berat;
            $yearly_total_bayar += $data->total_bayar;
            $yearly_total_tagihan += $data->total_tagihan;
            $yearly_total_profit += $data->profit;

        }

        $grand_total_berat = 0;
        $grand_total_bayar = 0;
        $grand_total_tagihan = 0;
        $grand_total_profit = 0;

        foreach ($bulanan as $data) {
            $day = date('j', strtotime($data->tanggal)); // get the day of the month

            $statistics_monthly[$day]['total_berat'] += $data->berat;
            $statistics_monthly[$day]['total_bayar'] += $data->total_bayar;
            $statistics_monthly[$day]['total_tagihan'] += $data->total_tagihan;
            $statistics_monthly[$day]['total_profit'] += $data->profit;

            $grand_total_berat += $data->berat;
            $grand_total_bayar += $data->total_bayar;
            $grand_total_tagihan += $data->total_tagihan;
            $grand_total_profit += $data->profit;

        }

        $pdf = PDF::loadview('rekap.statistik.pdf', [
            'customer' => $customer,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'dataTahun' => $dataTahun,
            'nama_bulan' => $nama_bulan,
            'statistics_monthly' => $statistics_monthly,
            'statistics_yearly' => $statistics_yearly,
            'grand_total_berat' => $grand_total_berat,
            'grand_total_bayar' => $grand_total_bayar,
            'grand_total_tagihan' => $grand_total_tagihan,
            'grand_total_profit' => $grand_total_profit,
            'yearly_total_berat' => $yearly_total_berat,
            'yearly_total_bayar' => $yearly_total_bayar,
            'yearly_total_tagihan' => $yearly_total_tagihan,
            'yearly_total_profit' => $yearly_total_profit,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('Statistik '.$customer->nama.'.pdf');
    }
}
