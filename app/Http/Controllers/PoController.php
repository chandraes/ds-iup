<?php

namespace App\Http\Controllers;

use App\Models\Config;
use App\Models\db\Pajak;
use App\Models\PurchaseOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Riskihajar\Terbilang\Facades\Terbilang;

class PoController extends Controller
{
    public function index()
    {
        return view('po.index');
    }

    public function form()
    {
        $ppn = Pajak::where('untuk', 'ppn')->first()->persen;

        return view('po.form-po',[
            'ppn' => $ppn,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kepada' => 'required',
            'alamat' => 'required',
            'telepon' => 'required',
            'apa_ppn' => 'required',
            'kategori' => 'required|array',
            'kategori.*' => 'required',
            'nama_barang' => 'required|array',
            'nama_barang.*' => 'required',
            'jumlah' => 'required|array',
            'jumlah.*' => 'required',
            'harga_satuan' => 'required|array',
            'harga_satuan.*' => 'required',
            'catatan' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            $db = new PurchaseOrder();

            $data['nomor'] = $db->generateNomor();
            $data['user_id'] = auth()->user()->id;
            $data['full_nomor'] = $db->generateFullNomor();

            $purchaseOrder = PurchaseOrder::create([
                'kepada' => $data['kepada'],
                'alamat' => $data['alamat'],
                'telepon' => $data['telepon'],
                'nomor' => $data['nomor'],
                'full_nomor' => $data['full_nomor'],
                'apa_ppn' => $data['apa_ppn'],
                'user_id' => $data['user_id'],
            ]);

            foreach ($data['kategori'] as $index => $kategori) {

                $data['jumlah'][$index] = str_replace('.', '', $data['jumlah'][$index]);
                $data['harga_satuan'][$index] = str_replace('.', '', $data['harga_satuan'][$index]);

                $purchaseOrder->items()->create([
                    'kategori' => $kategori,
                    'nama_barang' => $data['nama_barang'][$index],
                    'jumlah' => $data['jumlah'][$index],
                    'harga_satuan' => $data['harga_satuan'][$index],
                    'total' => $data['jumlah'][$index] * $data['harga_satuan'][$index],
                ]);
            }

            if (!empty($data['catatan'])) {
                foreach ($data['catatan'] as $catatan) {
                    $purchaseOrder->notes()->create([
                        'note' => $catatan,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('po')->with('success', 'Purchase Order berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data. '.$e->getMessage()]);
        }

    }

    public function rekap(Request $request)
    {
        $db = new PurchaseOrder();
        $dataTahun = $db->dataTahun();

        $tahun = $request->get('tahun') ?? now()->year;

        $data = PurchaseOrder::whereYear('created_at', $tahun)->get();


        return view('po.rekap', [
            'data' => $data,
            'dataTahun' => $dataTahun,
            'tahun' => $tahun,
        ]);
    }

    public function pdf(PurchaseOrder $po)
    {
        $pt = Config::where('untuk', 'resmi')->first();
        $ppn = Pajak::where('untuk', 'ppn')->first()->persen;

        if($po->apa_ppn == 1){
            $total = $po->items->sum('total') + ($po->items->sum('total') * $ppn / 100);
        } else {
            $total = $po->items->sum('total');
        }

        $terbilang = Terbilang::make($total);

        // dd($terbilang, $po->items->sum('total'));

        $pdf = PDF::loadview('po.po-pdf', [
            'data' => $po->load('notes', 'items'),
            'pt' => $pt,
            'ppn' => $ppn,
            'terbilang' => $terbilang,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream($po->full_nomor.'.pdf');
    }

    public function delete(PurchaseOrder $po)
    {
        try {
            DB::beginTransaction();

            $po->items()->delete();
            $po->notes()->delete();
            $po->delete();

            DB::commit();

            return redirect()->route('po.rekap')->with('success', 'Purchase Order berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan saat menghapus data. '.$e->getMessage()]);
        }
    }
}
