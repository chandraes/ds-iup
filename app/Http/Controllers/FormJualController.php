<?php

namespace App\Http\Controllers;

use App\Models\Config;
use App\Models\db\Barang\BarangStokHarga;
use App\Models\db\Konsumen;
use App\Models\db\Pajak;
use App\Models\transaksi\InvoiceJual;
use App\Models\transaksi\KeranjangJual;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class FormJualController extends Controller
{

    public function get_konsumen(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|exists:konsumens,id'
        ]);

        $konsumen = Konsumen::find($data['id']);

        return response()->json($konsumen);
    }

    public function keranjang_store(Request $request)
    {
        $data = $request->validate([
            'barang_stok_harga_id' => 'required|exists:barang_stok_hargas,id',
            'jumlah' => 'required',
            'barang_ppn' => 'required',
        ]);

        $product = BarangStokHarga::find($data['barang_stok_harga_id']);

        if ($data['jumlah'] == 0 || $data['jumlah'] > $product->stok || $product->harga == 0) {
            $errorMessage = $data['jumlah'] == 0 || $data['jumlah'] > $product->stok
                ? 'Jumlah stok tidak mencukupi!'
                : 'Harga jual barang belum diatur!';
            return redirect()->back()->with('error', $errorMessage);
        }

        $ppnValue = $data['barang_ppn'];
        $oppositePpnValue = $ppnValue == 1 ? 0 : 1;

        $checkKeranjang = KeranjangJual::where('barang_ppn', $oppositePpnValue)->first();
        if ($checkKeranjang) {
            $errorMessage = $ppnValue == 1
                ? 'Keranjang sudah terisi dengan barang non ppn. Silahkan hapus barang non ppn terlebih dahulu'
                : 'Keranjang sudah terisi dengan barang ppn. Silahkan hapus barang ppn terlebih dahulu';
            return redirect()->back()->with('error', $errorMessage);
        }



        $data['user_id'] = auth()->user()->id;
        $data['jumlah'] = str_replace('.', '', $data['jumlah']);
        $data['barang_id'] = $product->barang_id;
        $data['harga_satuan'] = $product->harga;
        $data['total'] = $data['jumlah'] * $data['harga_satuan'];

        KeranjangJual::create($data);

        return redirect()->back()->with('success', 'Barang berhasil ditambahkan ke keranjang');
    }

    public function keranjang_update(Request $request)
    {
        $productId = $request->input('barang_stok_harga_id');
        $quantity = $request->input('quantity');

        $product = BarangStokHarga::find($productId);
        $cartItem = KeranjangJual::where('barang_stok_harga_id', $productId)->first();

        if ($cartItem) {
            $newQuantity = $cartItem->jumlah + $quantity;
            if ($newQuantity > $product->stok) {
                return response()->json(['success' => false, 'message' => 'Jumlah item melebihi stok yang tersedia.']);
            }
            $cartItem->jumlah = $newQuantity;
            if ($cartItem->jumlah <= 0) {
                $cartItem->delete();
            } else {
                $cartItem->save();
            }
        } else {
            if ($quantity > $product->stok) {
                return response()->json(['success' => false, 'message' => 'Jumlah item melebihi stok yang tersedia.']);
            }
            KeranjangJual::create([
                'user_id' => auth()->user()->id,
                'barang_ppn' => $product->barang->jenis == 1 ? 1 : 0,
                'barang_id' => $product->barang_id,
                'barang_stok_harga_id' => $productId,
                'jumlah' => $quantity,
                'harga_satuan' => $product->harga,
                'total' => $quantity * $product->harga
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function keranjang_set(Request $request)
    {
        $productId = $request->input('barang_stok_harga_id');
        $quantity = $request->input('quantity');

        $product = BarangStokHarga::find($productId);

        if ($quantity > $product->stok) {
            return response()->json(['success' => false, 'message' => 'Jumlah item melebihi stok yang tersedia.']);
        }

        $cartItem = KeranjangJual::where('barang_stok_harga_id', $productId)->first();

        if ($cartItem) {
            $cartItem->jumlah = $quantity;
            if ($cartItem->jumlah <= 0) {
                $cartItem->delete();
            } else {
                $cartItem->save();
            }
        } else {
            KeranjangJual::create([
                'user_id' => auth()->user()->id,
                'barang_ppn' => $product->barang->jenis == 1 ? 1 : 0,
                'barang_id' => $product->barang_id,
                'barang_stok_harga_id' => $productId,
                'jumlah' => $quantity,
                'harga_satuan' => $product->harga,
                'total' => $quantity * $product->harga
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function keranjang_empty()
    {
        $keranjang = KeranjangJual::where('user_id', auth()->user()->id)->get();

        if ($keranjang->isEmpty()) {
            return redirect()->back()->with('error', 'Keranjang sudah kosong');
        }

        KeranjangJual::where('user_id', auth()->user()->id)->delete();


        return redirect()->back()->with('success', 'Keranjang berhasil dikosongkan');
    }

    public function keranjang()
    {

        $keranjang = KeranjangJual::with('stok')->where('user_id', auth()->user()->id)->get();
        $dbPajak = new Pajak();
        $total = KeranjangJual::where('user_id', auth()->user()->id)->sum('total');
        $ppn = $dbPajak->where('untuk', 'ppn')->first()->persen;
        $nominalPpn = KeranjangJual::where('user_id', auth()->user()->id)->where('barang_ppn', 1)->first() ? ($total * $ppn / 100) : 0;
        $pphVal = $dbPajak->where('untuk', 'pph')->first()->persen;
        $konsumen = Konsumen::where('active', 1)->get();

        Carbon::setLocale('id');

        // Format the date
        $tanggal = Carbon::now()->translatedFormat('d F Y');
        $jam = Carbon::now()->translatedFormat('H:i');


        $db = new InvoiceJual();

        $kode = $db->generateKode($keranjang->first()->barang_ppn);

        return view('billing.stok.keranjang-jual', [
            'keranjang' => $keranjang,
            'ppn' => $ppn,
            'total' => $total,
            'pphVal' => $pphVal,
            'nominalPpn' => $nominalPpn,
            'konsumen' => $konsumen,
            'tanggal' => $tanggal,
            'jam' => $jam,
            'kode' => $kode,
        ]);
    }

    public function keranjang_checkout(Request $request)
    {
        $data = $request->validate([
            'konsumen_id' => 'required',
            'diskon' => 'required',
            'add_fee' => 'required',
            'nama' => 'required_if:konsumen_id,*',
            'no_hp' => 'required_if:konsumen_id,*',
            'npwp' => 'nullable',
            'alamat' => 'nullable',
            'dp' => 'nullable',
        ]);

        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        $db = new KeranjangJual();

        $res = $db->checkout($data);
        // dd($res['invoice']->id);
        if ($res['status'] == 'success') {
            $untuk = $res['invoice']->kas_ppn == 1 ? 'resmi' : 'non-resmi';
            $pt = Config::where('untuk', $untuk)->first();
            Carbon::setLocale('id');

            $jam = CarbonImmutable::parse($res['invoice']->created_at)->translatedFormat('H:i');
            $tanggal = CarbonImmutable::parse($res['invoice']->created_at)->translatedFormat('d F Y');

            $pdf = PDF::loadview('billing.stok.invoice-pdf', [
                'data' => $res['invoice']->load('konsumen', 'invoice_detail.stok.type', 'invoice_detail.stok.barang', 'invoice_detail.stok.unit', 'invoice_detail.stok.kategori', 'invoice_detail.stok.barang_nama'),
                'pt' => $pt,
                'jam' => $jam,
                'tanggal' => $tanggal,
            ])->setPaper('a4', 'portrait');

            $directory = storage_path('app/public/invoices');
            $pdfPath = $directory . '/invoice-' . $res['invoice']->id . '.pdf';

            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            $pdf->save($pdfPath);

            $pdfUrl = asset('storage/invoices/invoice-' . $res['invoice']->id . '.pdf');

            return redirect()->route('billing.lihat-stok')
                ->with('status', 'success')
                ->with('message', 'Invoice berhasil dibuat.')
                ->with('pdfUrl', $pdfUrl);
        }


        return redirect()->route('billing.lihat-stok')->with($res['status'], $res['message']);
    }




}
