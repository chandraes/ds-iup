<?php

namespace App\Http\Controllers;

use App\Models\db\InventarisJenis;
use App\Models\db\InventarisKategori;
use App\Models\db\InventarisRekap;
use App\Models\transaksi\InventarisInvoice;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InventarisController extends Controller
{
    public function index()
    {
        $kategori = InventarisKategori::has('jenis')->get();

        return view('inventaris.index', [
            'kategori' => $kategori
        ]);
    }

    public function detail(InventarisKategori $kategori)
    {
        $data = $kategori->load(['jenis.rekap']);
        return view('inventaris.detail.index', [
            'data' => $data,
            'kategori' => $kategori
        ]);
    }

    public function detail_jenis(InventarisKategori $kategori, InventarisJenis $jenis)
    {
        $data = $jenis->rekap;

        return view('inventaris.detail.detail', [
            'kategori' => $kategori,
            'data' => $data,
            'inventaris' => $jenis->load('kategori'),
        ]);
    }

    public function aksi(InventarisKategori $kategori, InventarisJenis $jenis, InventarisRekap $inventaris, Request $request)
    {
        $data = $request->validate([
            'uraian' => 'required',
            'jumlah' => 'required',
            'harga_satuan' => 'required',
        ]);

        $db = new InventarisRekap();

        $res = $db->createRekap($data, $inventaris->id);

        return redirect()->back()->with($res['status'], $res['message']);
    }

    public function invoice(Request $request)
    {
        $db = new InventarisInvoice();

        $dataTahun = $db->dataTahun();

        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');

        $data = $db->rekapInvoice($bulan, $tahun);

        $bulanSebelumnya = $bulan - 1;
        $bulanSebelumnya = $bulanSebelumnya == 0 ? 12 : $bulanSebelumnya;
        $tahunSebelumnya = $bulanSebelumnya == 12 ? $tahun - 1 : $tahun;
        $stringBulan = Carbon::createFromDate($tahun, $bulanSebelumnya)->locale('id')->monthName;
        $stringBulanNow = Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;

        return view('inventaris.rekap.index', [
            'data' => $data,
            'dataTahun' => $dataTahun,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'stringBulan' => $stringBulan,
            'stringBulanNow' => $stringBulanNow,
            'bulanSebelumnya' => $bulanSebelumnya,
            'tahunSebelumnya' => $tahunSebelumnya,
        ]);
    }


}
