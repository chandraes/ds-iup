<?php

namespace App\Http\Controllers;

use App\Models\Pajak\RekapMasukanDetail;
use App\Models\Pajak\RekapPpn;
use App\Models\PpnKeluaran;
use App\Models\PpnMasukan;
use App\Models\transaksi\InventarisInvoice;
use App\Models\transaksi\InvoiceBelanja;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PajakController extends Controller
{
    public function index()
    {
        return view('pajak.index');
    }

    public function ppn_masukan()
    {
        $db = new PpnMasukan();

        $data = $db->with(['invoiceBelanja.supplier'])->where('is_keranjang', 0)->where('is_finish', 0)->get();
        $keranjang = $db->with(['invoiceBelanja.supplier'])->where('is_keranjang', 1)->where('is_finish', 0)->count();
        $keranjangData = $db->with(['invoiceBelanja.supplier'])->where('is_keranjang', 1)->where('is_finish', 0)->get();

        $total_faktur = 0;
        $total_blm_faktur = 0;

        foreach ($data as $item) {
            if ($item->is_faktur == 1) {
                $total_faktur += $item->nominal;
            } elseif ($item->is_faktur == 0) {
                $total_blm_faktur += $item->nominal;
            }
        }

        return view('pajak.ppn-masukan.index', [
            'data' => $data,
            'total_faktur' => $total_faktur,
            'total_blm_faktur' => $total_blm_faktur,
            'keranjang' => $keranjang,
            'keranjangData' => $keranjangData
        ]);
    }

    public function ppn_masukan_store_faktur(Request $request, PpnMasukan $ppnMasukan)
    {
        $data = $request->validate([
            'no_faktur' => 'required',
        ]);

        $ppnMasukan->update([
            'is_faktur' => 1,
            'no_faktur' => $data['no_faktur']
        ]);

        return redirect()->back()->with('success', 'Berhasil menyimpan data');
    }

    public function ppn_masukan_keranjang_store(Request $request)
    {
        $data = $request->validate([
            'selectedData' => 'required',
        ]);

        $data['selectedData'] = trim($data['selectedData'], ',');
        $data['selectedData'] = explode(',', $data['selectedData']);

        $db = new PpnMasukan();

        $db->whereIn('id', $data['selectedData'])->update([
            'is_keranjang' => 1
        ]);

        return redirect()->back()->with('success', 'Berhasil menyimpan data');


    }

    public function ppn_masukan_keranjang_destroy(PPnMasukan $ppnMasukan)
    {
        $ppnMasukan->update([
            'is_keranjang' => 0
        ]);

        return redirect()->back()->with('success', 'Berhasil menghapus data dari keranjang!');
    }

    public function ppn_masukan_keranjang_lanjut()
    {
        $db = new RekapPpn();

        $res = $db->keranjang_masukan_lanjut();

        return redirect()->back()->with($res['status'], $res['message']);

    }

    public function ppn_keluaran(Request $request)
    {
        $db = new PpnKeluaran();

        $data = $db->with('invoiceJual.konsumen', 'invoiceJual.konsumen_temp')->where('is_keranjang', 0)->where('is_finish', 0)->get();

        $total_faktur = 0;
        $total_blm_faktur = 0;

        foreach ($data as $item) {
            if ($item->is_faktur == 1) {
                $total_faktur += $item->nominal;
            } elseif ($item->is_faktur == 0) {
                $total_blm_faktur += $item->nominal;
            }
        }

        return view('pajak.ppn-keluaran.index', [
            'data' => $data,
            'total_faktur' => $total_faktur,
            'total_blm_faktur' => $total_blm_faktur
        ]);

    }

    public function ppn_keluaran_store_faktur(Request $request, PpnKeluaran $ppnKeluaran)
    {
        $data = $request->validate([
            'no_faktur' => 'required',
        ]);

        $ppnKeluaran->update([
            'is_faktur' => 1,
            'no_faktur' => $data['no_faktur']
        ]);

        return redirect()->back()->with('success', 'Berhasil menyimpan data');
    }

    public function rekap_ppn(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');

        $db = new RekapPpn();

        $data = $db->rekapByMonth($bulan, $tahun);
        $dataTahun = $db->dataTahun();

        $bulanSebelumnya = $bulan - 1;
        $bulanSebelumnya = $bulanSebelumnya == 0 ? 12 : $bulanSebelumnya;
        $tahunSebelumnya = $bulanSebelumnya == 12 ? $tahun - 1 : $tahun;
        $stringBulan = Carbon::createFromDate($tahun, $bulanSebelumnya)->locale('id')->monthName;
        $stringBulanNow = Carbon::createFromDate($tahun, $bulan)->locale('id')->monthName;

        $dataSebelumnya = $db->rekapByMonthSebelumnya($bulanSebelumnya, $tahunSebelumnya);

        return view('pajak.rekap-ppn.index', [
            'data' => $data,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'dataTahun' => $dataTahun,
            'dataSebelumnya' => $dataSebelumnya,
            'stringBulan' => $stringBulan,
            'stringBulanNow' => $stringBulanNow,
            'bulanSebelumnya' => $bulanSebelumnya,
            'tahunSebelumnya' => $tahunSebelumnya
        ]);
    }

    public function rekap_ppn_masukan_detail(RekapPpn $rekapPpn)
    {
        $masukan_id = $rekapPpn->masukan_id;
        $dataMasukan = RekapMasukanDetail::where('masukan_id', $masukan_id)->pluck('ppn_masukan_id');
        // dd($dataMasukan);
        $db = new PpnMasukan();
        $data = $db->with(['invoiceBelanja.supplier'])->whereIn('id', $dataMasukan)->get();

        return view('pajak.rekap-ppn.masukan-detail', [
            'data' => $data
        ]);
    }
}
