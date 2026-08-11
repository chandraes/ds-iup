<?php

namespace App\Http\Controllers;

use App\Models\Pajak\RekapKeluaranDetail;
use App\Models\Pajak\RekapMasukanDetail;
use App\Models\Pajak\RekapPpn;
use App\Models\PpnKeluaran;
use App\Models\PpnMasukan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class PajakController extends Controller
{
    public function index()
    {
        return view('pajak.index');
    }

    public function ppn_masukan()
    {
        $db = new PpnMasukan;

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
            'keranjangData' => $keranjangData,
        ]);
    }

    public function ppn_masukan_store_faktur(Request $request, PpnMasukan $ppnMasukan)
    {
        $data = $request->validate([
            'no_faktur' => 'required',
        ]);

        $ppnMasukan->update([
            'is_faktur' => 1,
            'no_faktur' => $data['no_faktur'],
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

        $db = new PpnMasukan;

        $db->whereIn('id', $data['selectedData'])->update([
            'is_keranjang' => 1,
        ]);

        return redirect()->back()->with('success', 'Berhasil menyimpan data');

    }

    public function ppn_masukan_keranjang_destroy(PPnMasukan $ppnMasukan)
    {
        $ppnMasukan->update([
            'is_keranjang' => 0,
        ]);

        return redirect()->back()->with('success', 'Berhasil menghapus data dari keranjang!');
    }

    public function ppn_masukan_keranjang_lanjut(Request $request)
    {

        $data = $request->validate([
            'penyesuaian' => 'required',
        ]);

        $db = new RekapPpn;
        $penyesuaian = str_replace('.', '', $data['penyesuaian']);
        $res = $db->keranjang_masukan_lanjut($penyesuaian);

        return redirect()->back()->with($res['status'], $res['message']);

    }

   public function ppn_keluaran(Request $request)
    {
        $db = new PpnKeluaran;

        // Jika request dari DataTables (AJAX)
        if ($request->ajax()) {
            $data = DB::table('ppn_keluarans')
                    ->leftJoin('invoice_juals', 'ppn_keluarans.invoice_jual_id', '=', 'invoice_juals.id')
                    ->leftJoin('konsumens', 'invoice_juals.konsumen_id', '=', 'konsumens.id')
                    ->leftJoin('kode_tokos', 'konsumens.kode_toko_id', '=', 'kode_tokos.id')
                    ->leftJoin('konsumen_temps', 'invoice_juals.konsumen_temp_id', '=', 'konsumen_temps.id')
                    ->where('ppn_keluarans.is_keranjang', 0)
                    ->where('ppn_keluarans.is_expired', 0)
                    ->where('ppn_keluarans.is_finish', 0)
                    ->select(
                        'ppn_keluarans.id',
                        'ppn_keluarans.nominal',
                        'ppn_keluarans.is_faktur',
                        'ppn_keluarans.no_faktur',
                        'ppn_keluarans.uraian',
                        'invoice_juals.id as invoice_jual_id',
                        'invoice_juals.kode as nota',
                        'invoice_juals.updated_at as tanggal',
                        'konsumens.nama as konsumen_nama',
                        'konsumens.nik as konsumen_nik',
                        'konsumens.npwp as konsumen_npwp',
                        'kode_tokos.kode as kode_toko',
                        'konsumen_temps.nama as temp_nama',
                        'konsumen_temps.npwp as temp_npwp'
                    );

           return DataTables::of($data)
                ->addColumn('checkbox', function($d) {
                    $disabled = $d->is_faktur == 0 ? 'disabled' : '';
                    return '<input style="height: 25px; width:25px" type="checkbox" value="'.$d->id.'" data-tagihan="'.$d->nominal.'" onclick="check(this, '.$d->id.')" id="idSelect-'.$d->id.'" class="dt-checkbox" '.$disabled.'>';
                })
                ->addColumn('tanggal', function($d) {
                    return $d->tanggal ?? '-';
                })
                ->addColumn('nota', function($d) {
                    if ($d->nota) {
                        return '<a href="'.route('billing.invoice-konsumen.detail', ['invoice' => $d->invoice_jual_id]).'">'.$d->nota.'</a>';
                    }
                    return '-';
                })
                ->addColumn('konsumen', function($d) {
                    if ($d->konsumen_nama) {
                        $kodeToko = $d->kode_toko ? $d->kode_toko . ' ' : '';
                        return $kodeToko . $d->konsumen_nama;
                    }
                    return $d->temp_nama ?? '-';
                })
                ->addColumn('nik_npwp', function($d) {
                    $nik = $d->konsumen_nik ?? '-';
                    $npwpRaw = $d->konsumen_npwp ?? $d->temp_npwp;
                    $npwp = $npwpRaw ? str_replace(['.', '-'], '', $npwpRaw) : '-';

                    return "NIK : {$nik}<br>NPWP : {$npwp}";
                })
                ->addColumn('non_npwp', function($d) {
                    $npwpRaw = $d->konsumen_npwp ?? $d->temp_npwp;
                    $nf_nominal = number_format($d->nominal, 0, ',', '.');

                    if ($d->is_faktur == 0 && strlen((string)$npwpRaw) < 10) return $nf_nominal;
                    return '0';
                })
                ->addColumn('npwp', function($d) {
                    $npwpRaw = $d->konsumen_npwp ?? $d->temp_npwp;
                    $nf_nominal = number_format($d->nominal, 0, ',', '.');

                    if ($d->is_faktur == 0 && strlen((string)$npwpRaw) >= 10) return $nf_nominal;
                    return '0';
                })
                ->addColumn('faktur', function($d) {
                    if ($d->is_faktur == 1) {
                        $nf_nominal = number_format($d->nominal, 0, ',', '.');
                        $noFaktur = $d->no_faktur ?? 'Faktur Belum Terisi';
                        return '<a href="#" onclick="showFaktur(\''.$noFaktur.'\')" data-bs-toggle="modal" data-bs-target="#showModal">'.$nf_nominal.'</a>';
                    }
                    return '0';
                })
                ->addColumn('action', function($d) {
                    $html = '';
                    $npwpRaw = $d->konsumen_npwp ?? $d->temp_npwp;

                    if ($d->is_faktur == 0 && strlen((string)$npwpRaw) < 10) {
                        $html .= '<form action="'.route('pajak.ppn-keluaran.expired', ['ppnKeluaran' => $d->id]).'" method="post" class="d-inline expired-form" id="expiredForm'.$d->id.'" data-id="'.$d->id.'">
                            '.csrf_field().'
                            <button type="submit" class="btn btn-danger btn-sm">Expired</button>
                        </form> ';
                    }

                    $btnClass = $d->is_faktur == 1 ? 'warning' : 'primary';
                    $btnText = $d->is_faktur == 1 ? 'Ubah Faktur' : 'Faktur';
                    $nota = $d->nota ?? 'Nota Belum Terisi';
                    $noFaktur = $d->is_faktur == 1 ? $d->no_faktur : '';
                    $nf_nominal = number_format($d->nominal, 0, ',', '.');

                    $html .= '<button type="button" class="btn btn-'.$btnClass.' btn-sm" data-bs-toggle="modal" data-bs-target="#modalFaktur" onclick="faktur('.$d->id.', \''.$nota.'\', \''.$nf_nominal.'\', '.$d->is_faktur.', \''.$noFaktur.'\')">'.$btnText.'</button>';

                    return $html;
                })

                // 2. Sesuaikan urutan kolom (Sorting) menggunakan nama tabel yang di join
                ->orderColumn('tanggal', function ($query, $order) { $query->orderBy('invoice_juals.updated_at', $order); })
                ->orderColumn('nota', function ($query, $order) { $query->orderBy('invoice_juals.kode', $order); })
                ->orderColumn('konsumen', function ($query, $order) { $query->orderBy('konsumens.nama', $order)->orderBy('konsumen_temps.nama', $order); })
                ->orderColumn('nik_npwp', function ($query, $order) { $query->orderBy('konsumens.npwp', $order); })
                ->orderColumn('non_npwp', function ($query, $order) { $query->orderBy('ppn_keluarans.nominal', $order); })
                ->orderColumn('npwp', function ($query, $order) { $query->orderBy('ppn_keluarans.nominal', $order); })
                ->orderColumn('faktur', function ($query, $order) { $query->orderBy('ppn_keluarans.nominal', $order); })
                ->filterColumn('konsumen', function($query, $keyword) {
                    $query->where(function($q) use ($keyword) {
                        $q->where('konsumens.nama', 'like', "%{$keyword}%")
                          ->orWhere('kode_tokos.kode', 'like', "%{$keyword}%")
                          ->orWhere('konsumen_temps.nama', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('nik_npwp', function($query, $keyword) {
                    $query->where(function($q) use ($keyword) {
                        $q->where('konsumens.nik', 'like', "%{$keyword}%")
                          ->orWhere('konsumens.npwp', 'like', "%{$keyword}%")
                          ->orWhere('konsumen_temps.npwp', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('nota', function($query, $keyword) {
                    $query->where('invoice_juals.kode', 'like', "%{$keyword}%");
                })
                ->filterColumn('tanggal', function($query, $keyword) {
                    $query->where('invoice_juals.updated_at', 'like', "%{$keyword}%");
                })
                ->rawColumns(['checkbox', 'nota', 'nik_npwp', 'faktur', 'action'])
                ->make(true);
        }

        // Hitung grand total
        $totals = DB::table('ppn_keluarans')
            ->leftJoin('invoice_juals', 'ppn_keluarans.invoice_jual_id', '=', 'invoice_juals.id')
            ->leftJoin('konsumens', 'invoice_juals.konsumen_id', '=', 'konsumens.id')
            ->leftJoin('konsumen_temps', 'invoice_juals.konsumen_temp_id', '=', 'konsumen_temps.id')
            ->where('ppn_keluarans.is_keranjang', 0)
            ->where('ppn_keluarans.is_expired', 0)
            ->where('ppn_keluarans.is_finish', 0)
            ->selectRaw("
                SUM(CASE WHEN ppn_keluarans.is_faktur = 0 AND LENGTH(COALESCE(konsumens.npwp, konsumen_temps.npwp, '')) < 10 THEN ppn_keluarans.nominal ELSE 0 END) as total_non_npwp,
                SUM(CASE WHEN ppn_keluarans.is_faktur = 0 AND LENGTH(COALESCE(konsumens.npwp, konsumen_temps.npwp, '')) >= 10 THEN ppn_keluarans.nominal ELSE 0 END) as total_npwp,
                SUM(CASE WHEN ppn_keluarans.is_faktur = 1 THEN ppn_keluarans.nominal ELSE 0 END) as total_faktur
            ")
            ->first();

        // Petakan hasilnya ke variabel yang akan dilempar ke Blade
        $totalNonNpwp = $totals->total_non_npwp ?? 0;
        $totalNpwp = $totals->total_npwp ?? 0;
        $totalFaktur = $totals->total_faktur ?? 0;

        $keranjang = $db->where('is_keranjang', 1)->where('is_finish', 0)->count();

        return view('pajak.ppn-keluaran.index', [
            'keranjang' => $keranjang,
            'totalNonNpwp' => $totalNonNpwp,
            'totalNpwp' => $totalNpwp,
            'totalFaktur' => $totalFaktur,
        ]);
    }

    public function ppn_keluaran_store_faktur(Request $request, PpnKeluaran $ppnKeluaran)
    {
        $data = $request->validate([
            'no_faktur' => 'required',
        ]);

        $ppnKeluaran->update([
            'is_faktur' => 1,
            'no_faktur' => $data['no_faktur'],
        ]);

        return redirect()->back()->with('success', 'Berhasil menyimpan data');
    }

    public function ppn_keluaran_keranjang_store(Request $request)
    {
        $data = $request->validate([
            'selectedData' => 'required',
        ]);

        $data['selectedData'] = trim($data['selectedData'], ',');
        $data['selectedData'] = explode(',', $data['selectedData']);

        $db = new PpnKeluaran;

        $db->whereIn('id', $data['selectedData'])->update([
            'is_keranjang' => 1,
        ]);

        return redirect()->back()->with('success', 'Berhasil menyimpan data');
    }

    public function ppn_keluaran_keranjang_destroy(PpnKeluaran $ppnKeluaran)
    {
        $ppnKeluaran->update([
            'is_keranjang' => 0,
        ]);

        return redirect()->back()->with('success', 'Berhasil menghapus data dari keranjang!');
    }

    public function ppn_keluaran_keranjang()
    {
        $db = new PpnKeluaran;
        $data = $db->with('invoiceJual.konsumen', 'invoiceJual.konsumen_temp')->where('is_keranjang', 1)->where('is_finish', 0)->get();
        $dbRekap = new RekapPpn;
        $saldoMasukan = $dbRekap->saldoTerakhir();

        $dariKas = 0;

        if (($saldoMasukan - $data->where('dipungut', 1)->sum('nominal')) < 0) {
            $dariKas = abs($saldoMasukan - $data->where('dipungut', 1)->sum('nominal'));
        }

        $dariKas = number_format($dariKas, 0, ',', '.');

        return view('pajak.ppn-keluaran.keranjang', [
            'data' => $data,
            'saldoMasukan' => $saldoMasukan,
            'dariKas' => $dariKas,
        ]);
    }

    public function ppn_keluaran_keranjang_lanjut(Request $request)
    {
        $data = $request->validate([
            'penyesuaian' => 'required',
        ]);

        $penyesuaian = str_replace('.', '', $data['penyesuaian']);

        $db = new RekapPpn;

        $res = $db->keranjang_keluaran_lanjut($penyesuaian);

        return redirect()->route('pajak.ppn-keluaran')->with($res['status'], $res['message']);
    }

    public function ppn_keluaran_expired(PpnKeluaran $ppnKeluaran)
    {
        $ppnKeluaran->update([
            'is_expired' => 1,
        ]);

        return redirect()->back()->with('success', 'Berhasil mengubah status menjadi expired!');
    }

    public function rekap_ppn(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');

        $db = new RekapPpn;

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
            'tahunSebelumnya' => $tahunSebelumnya,
        ]);
    }

    public function rekap_ppn_masukan_detail(RekapPpn $rekapPpn)
    {
        $masukan_id = $rekapPpn->masukan_id;
        $dataMasukan = RekapMasukanDetail::where('masukan_id', $masukan_id)->pluck('ppn_masukan_id');
        // dd($dataMasukan);
        $db = new PpnMasukan;
        $data = $db->with(['invoiceBelanja.supplier'])->whereIn('id', $dataMasukan)->get();

        return view('pajak.rekap-ppn.masukan-detail', [
            'data' => $data,
            'rekapPpn' => $rekapPpn,
        ]);
    }

    public function rekap_ppn_keluaran_Detail(RekapPpn $rekapPpn)
    {
        $keluaran_id = $rekapPpn->keluaran_id;
        $dataKeluaran = RekapKeluaranDetail::where('keluaran_id', $keluaran_id)->pluck('ppn_keluaran_id');

        $db = new PpnKeluaran;
        $data = $db->with(['invoiceJual.konsumen', 'invoiceJual.konsumen_temp'])->whereIn('id', $dataKeluaran)->get();

        return view('pajak.rekap-ppn.keluaran-detail', [
            'data' => $data,
            'rekapPpn' => $rekapPpn,
        ]);
    }

    public function ppn_expired()
    {
        $db = new PpnKeluaran;
        $data = $db->with(['invoiceJual.konsumen', 'invoiceJual.konsumen_temp'])->where('is_expired', 1)->get();

        return view('pajak.ppn-expired.index', [
            'data' => $data,
        ]);
    }

    public function ppn_expired_back(PpnKeluaran $ppnKeluaran)
    {
        $ppnKeluaran->update([
            'is_expired' => 0,
        ]);

        return redirect()->back()->with('success', 'Berhasil mengubah status menjadi tidak expired!');
    }
}
