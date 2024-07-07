<?php

namespace App\Http\Controllers;

use App\Models\db\Barang\Barang;
use App\Models\db\Barang\BarangKategori;
use App\Models\db\Barang\BarangUnit;
use App\Models\db\Pajak;
use App\Models\db\Supplier;
use App\Models\transaksi\Keranjang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormBeliController extends Controller
{
    public function index(Request $request)
    {
        $req = $request->validate([
            'kas_ppn' => 'required',
            'tempo' => 'required',
        ]);

        $supplier = Supplier::where('status', 1)->get();

        if($supplier->count() == 0) {
            return redirect()->back()->with('error', 'Belum ada supplier yang aktif, silahkan tambah supplier terlebih dahulu!');
        }

        $data = BarangUnit::with(['types'])->get();
        $ppn = Pajak::where('untuk', 'ppn')->first()->persen;

        $jenis = $req['kas_ppn'] == '1' ? 1 : 2;

        $keranjang = Keranjang::with(['barang.type.unit'])
                        ->where('user_id', auth()->user()->id)
                        ->where('jenis', $jenis)
                        ->where('tempo', $req['tempo'])->get();

        return view('billing.form-beli.index', [
            'data' => $data,
            'jenis' => $jenis, // 1 = 'tunai', 2 = 'kredit
            'req' => $req,
            'keranjang' => $keranjang,
            'ppnRate' => $ppn,
            'supplier' => $supplier,
        ]);
    }

    public function getSupplier(Request $request)
    {

        $data = Supplier::find($request->id);

        return response()->json($data);
    }

    public function getKategori(Request $request)
    {
        $barang = Barang::where('barang_type_id', $request->barang_type_id);
        // distinct barang_kategori_id from barang to array
        $kategori = $barang->distinct()->pluck('barang_kategori_id')->toArray();

        $data = BarangKategori::whereIn('id', $kategori)->get();

        if($data->count() == 0) {
            return response()->json([
                'status' => 0,
                'message' => 'Data tidak ditemukan',
            ]);
        }

        return response()->json([
            'status' => 1,
            'data' => $data,
        ]);
    }

    public function getBarang(Request $request)
    {
        $data = Barang::where('barang_kategori_id', $request->barang_kategori_id)
                ->where('barang_type_id', $request->barang_type_id)
                ->get();

        if($data->count() == 0) {
            return response()->json([
                'status' => 0,
                'message' => 'Data tidak ditemukan',
            ]);
        }

        return response()->json([
            'status' => 1,
            'data' => $data,
        ]);
    }

    public function keranjang_store(Request $request)
    {
        $data = $request->validate([
            'barang_id' => 'required|exists:barangs,id',
            'jumlah' => 'required',
            'harga' => 'required',
            'jenis' => 'required',
            'tempo' => 'required',
        ]);

        $data['user_id'] = auth()->user()->id;
        $data['jumlah'] = str_replace('.', '', $data['jumlah']);
        $data['harga'] = str_replace('.', '', $data['harga']);
        $data['total'] = $data['jumlah'] * $data['harga'];

        try {
            DB::beginTransaction();

            Keranjang::create($data);

            DB::commit();
            return back()->with('success', 'Data berhasil disimpan');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }

    }

    public function keranjang_delete(Keranjang $keranjang)
    {
        try {
            DB::beginTransaction();

            $keranjang->delete();

            DB::commit();
            return back()->with('success', 'Data berhasil dihapus');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function keranjang_empty(Request $request)
    {
        $req = $request->validate([
            'jenis' => 'required',
            'tempo' => 'required',
        ]);

        try {
            DB::beginTransaction();

            Keranjang::where('user_id', auth()->user()->id)
                ->where('jenis', $req['jenis'])
                ->where('tempo', $req['tempo'])
                ->delete();

            DB::commit();
            return back()->with('success', 'Keranjang berhasil dikosongkan');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function keranjang_checkout(Request $request)
    {
        $data = $request->validate([
            'kas_ppn' => 'required',
            'tempo' => 'required',
            'supplier_id' => 'required',
            'uraian' => 'required',
            'diskon' => 'required',
            'add_fee' => 'required',
            'jenis' => 'required',
            'dp' => 'required_if:tempo,1',
            'dp_ppn' => 'nullable',
            'jatuh_tempo' => 'required_if:tempo,1',
        ]);

        $db = new Keranjang();

        $res = $db->checkout($data);

        return redirect()->back()->with($res['status'], $res['message']);
    }
}
