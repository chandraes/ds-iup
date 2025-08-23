<?php

namespace App\Http\Controllers;

use App\Http\Traits\Terbilang;
use App\Models\Config;
use App\Models\db\Barang\BarangStokHarga;
use App\Models\db\DiskonUmum;
use App\Models\db\Konsumen;
use App\Models\db\Pajak;
use App\Models\GroupWa;
use App\Models\KasKonsumen;
use App\Models\KonsumenTemp;
use App\Models\Rekening;
use App\Models\transaksi\InvoiceJual;
use App\Models\transaksi\KeranjangJual;
use App\Services\StarSender;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Spatie\Browsershot\Browsershot;

class FormJualController extends Controller
{
    use Terbilang;

    public function get_konsumen(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|exists:konsumens,id',
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

        if ($product->min_jual == null) {
            return redirect()->back()->with('error', 'Barang tidak memiliki aturan minimal jual! Silahkan hubungi admin');
        }

        $minJual = $product->min_jual;

        if ($data['jumlah'] % $minJual != 0) {
            return redirect()->back()->with('error', 'Jumlah barang harus kelipatan dari '.$minJual.'!');
        }

        if ($data['jumlah'] == 0 || $data['jumlah'] > $product->stok || $product->harga == 0) {
            $errorMessage = $data['jumlah'] == 0 || $data['jumlah'] > $product->stok
                ? 'Jumlah stok tidak mencukupi!'
                : 'Harga jual barang belum diatur!';

            return redirect()->back()->with('error', $errorMessage);
        }

        $ppnValue = $data['barang_ppn'];
        $oppositePpnValue = $ppnValue == 1 ? 0 : 1;

        $checkKeranjang = KeranjangJual::where('barang_ppn', $oppositePpnValue)->where('user_id', auth()->user()->id)->first();
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
                'total' => $quantity * $product->harga,
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
                'total' => $quantity * $product->harga,
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
        $dbPajak = new Pajak;
        $total = KeranjangJual::where('user_id', auth()->user()->id)->sum('total');
        $ppn = $dbPajak->where('untuk', 'ppn')->first()->persen;
        $nominalPpn = KeranjangJual::where('user_id', auth()->user()->id)->where('barang_ppn', 1)->first() ? ($total * $ppn / 100) : 0;
        $pphVal = $dbPajak->where('untuk', 'pph')->first()->persen;
        $konsumen = Konsumen::with(['kode_toko'])->where('active', 1)->get();
        $ppnStore = $nominalPpn > 0 ? 1 : 0;
        $diskonUmum = DiskonUmum::select('untuk', 'persen', 'kode')->get();
        Carbon::setLocale('id');

        // Format the date
        $tanggal = Carbon::now()->translatedFormat('d F Y');
        $jam = Carbon::now()->translatedFormat('H:i');

        $db = new InvoiceJual;

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
            'diskonUmum' => $diskonUmum,
            'ppnStore' => $ppnStore,
        ]);
    }

    public function keranjang_checkout(Request $request)
    {
        $data = $request->validate([
            'konsumen_id' => 'required',
            'pembayaran' => 'required',
            'diskon' => 'required',
            'add_fee' => 'required',
            'nama' => 'required_if:konsumen_id,*',
            'no_hp' => 'required_if:konsumen_id,*',
            'npwp' => 'required_if:konsumen_id,*',
            'alamat' => 'nullable',
            'dp' => 'nullable',
            'dp_ppn' => 'nullable',
            'dipungut' => 'nullable',
        ]);

        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        if ($data['konsumen_id'] == '*' && $data['pembayaran'] != 1) {
            return redirect()->back()->with('error', 'Konsumen cash tidak bisa memilih sistem pembayaran lain selain cash');
        }

        $db = new KeranjangJual;

        $res = $db->checkout($data);
        // dd($res['invoice']->id);
        if ($res['status'] == 'success') {
            // $untuk = $res['invoice']->kas_ppn == 1 ? 'resmi' : 'non-resmi';
            // $pt = Config::where('untuk', $untuk)->first();
            // Carbon::setLocale('id');

            // $jam = CarbonImmutable::parse($res['invoice']->created_at)->translatedFormat('H:i');
            // $tanggal = CarbonImmutable::parse($res['invoice']->created_at)->translatedFormat('d F Y');

            // $pdf = PDF::loadview('billing.stok.invoice-pdf', [
            //     'data' => $res['invoice']->load('konsumen', 'invoice_detail.stok.type', 'invoice_detail.stok.barang', 'invoice_detail.stok.unit', 'invoice_detail.stok.kategori', 'invoice_detail.stok.barang_nama'),
            //     'pt' => $pt,
            //     'jam' => $jam,
            //     'tanggal' => $tanggal,
            // ])->setPaper('a4', 'portrait');

            // $directory = storage_path('app/public/invoices');
            // $pdfPath = $directory . '/invoice-' . $res['invoice']->id . '.pdf';

            // if (!file_exists($directory)) {
            //     mkdir($directory, 0755, true);
            // }

            // $pdf->save($pdfPath);

            // $pdfUrl = asset('storage/invoices/invoice-' . $res['invoice']->id . '.pdf');

            return redirect()->route('billing.form-jual.invoice', ['invoice' => $res['invoice']->id]);
        }

        return redirect()->route('billing.lihat-stok')->with($res['status'], $res['message']);
    }

    public function invoice(InvoiceJual $invoice)
    {
        $pt = Config::where('untuk', $invoice->kas_ppn == 1 ? 'resmi' : 'non-resmi')->first();
        Carbon::setLocale('id');

        $jam = CarbonImmutable::parse($invoice->created_at)->translatedFormat('H:i');
        $tanggal = CarbonImmutable::parse($invoice->created_at)->translatedFormat('d F Y');

        $tanggal_tempo = $invoice->sistem_pembayaran !== 1 ? Carbon::parse($invoice->jatuh_tempo)->translatedFormat('d F Y') : '-';

        $kas = $invoice->kas_ppn == 1 ? 'kas-besar-ppn' : 'kas-besar-non-ppn';
        $ppn = Pajak::where('untuk', 'ppn')->first()->persen;

        $rekening = Rekening::where('untuk', $kas)->first();

        $terbilang = $invoice->sistem_pembayaran !== 1 ? ucwords($this->pembilang($invoice->sisa_tagihan)) : ucwords($this->pembilang($invoice->grand_total));

        $pdf = PDF::loadview('billing.stok.invoice-pdf', [
            'data' => $invoice->loadMissing([
                'konsumen',
                'invoice_detail.stok.type',
                'invoice_detail.stok.barang',
                'invoice_detail.stok.barang.satuan',
                'invoice_detail.stok.unit',
                'invoice_detail.stok.kategori',
                'invoice_detail.stok.barang_nama',
            ]),
            'ppn' => $ppn,
            'pt' => $pt,
            'tanggal_tempo' => $tanggal_tempo,
            'tanggal' => $tanggal,
            'terbilang' => $terbilang,
            'rekening' => $rekening,
        ])->setPaper('a4', 'portrait');

        $directory = storage_path('app/public/invoices');
        $pdfPath = $directory.'/invoice-'.$invoice->id.'.pdf';

        // Check if the directory exists, if not, create it
        if (! file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        // Check if the PDF file already exists
        if (file_exists($pdfPath)) {
            // Delete the existing file
            unlink($pdfPath);
        }

        // Save the new PDF, overriding the existing one if it exists
        $pdf->save($pdfPath);

        // Generate the URL for the PDF
        $pdfUrl = asset('storage/invoices/invoice-'.$invoice->id.'.pdf');

        // convert it to be image
        // $pdf = new Pdf($pdfPath);

        $konsumen = $invoice->konsumen_id ? Konsumen::find($invoice->konsumen_id) : KonsumenTemp::find($invoice->konsumen_temp_id);

        if ($invoice->konsumen_id) {
            if ($konsumen->pembayaran == 1 || $invoice->lunas == 1) {
                $uraian = '*Cash*';
                $pembayaran = 'Lunas';
            } elseif ($konsumen->pembayaran == 2 && $invoice->titipan == 1) {
                $uraian = '*Titipan*';
                $pembayaran = 'Titipan';
            } else {
                if ($konsumen->pembayaran == 2 && $invoice->dp > 0) {
                    $uraian = '*DP*';
                } else {
                    $uraian = '*Tanpa DP*';
                }
                $pembayaran = $konsumen->sistem_pembayaran.' '.$konsumen->tempo_hari.' Hari';
            }
        } else {
            $uraian = '*Cash*';
            $pembayaran = 'Lunas';
        }

        if ($konsumen && $konsumen->no_hp && $invoice->send_wa == 0) {
            $tujuan = str_replace('-', '', $konsumen->no_hp);
            $pesan = "🟡🟡🟡🟡🟡🟡🟡🟡🟡\n".
                    "*Invoice Pembelian*\n".
                    "🟡🟡🟡🟡🟡🟡🟡🟡🟡\n\n".
                    $pt->nama."\n\n".
                    "No Invoice:\n".
                    '*'.$invoice->kode."*\n\n".
                    'Tanggal : '.$tanggal."\n".
                    'Jam       : '.$jam."\n\n".
                    'Uraian : '.$uraian."\n".
                    'Pembayaran : '.$pembayaran."\n";

            if ($invoice->konsumen_id && $invoice->lunas == 0 && $invoice->titipan == 0) {
                $jatuhTempo = Carbon::parse($invoice->jatuh_tempo)->translatedFormat('d-m-Y');
                $pesan .= 'Tgl Jatuh Tempo : '.$jatuhTempo."\n\n";

            }

            $nama_konsumen = $invoice->konsumen_id ? $konsumen->kode_toko->kode." ".$konsumen->nama : $konsumen->nama;

            $pesan .= 'Konsumen : *'.$nama_konsumen."*\n\n";
                    // 'Nilai DPP    : Rp '.number_format($invoice->total, 0, ',', '.')."\n";
            $pesan .= 'Total Tagihan : Rp '.number_format($invoice->grand_total, 0, ',', '.')."\n\n";

            // if ($invoice->kas_ppn == 1) {
            //     $pesan .= 'PPN         : Rp '.number_format($invoice->ppn, 0, ',', '.')."\n";
            // } else {
            //     $pesan .= "\n";
            // }

            // if ($invoice->lunas == 1) {

            // } else {
            if ($invoice->dp > 0) {
                $pesan .= 'DP      : Rp '.number_format($invoice->dp + $invoice->dp_ppn, 0, ',', '.')."\n\n".
                        'Sisa Tagihan : *Rp '.number_format($invoice->grand_total - $invoice->dp - $invoice->dp_ppn, 0, ',', '.')."*\n\n";
            }
                // else {
                //     $pesan .= 'Sisa Tagihan : *Rp '.number_format($invoice->grand_total, 0, ',', '.')."*\n\n";
                // }
            // }

            $pesan .= "==========================\n";

            if ($invoice->konsumen_id) {
                $sisaTerakhir = KasKonsumen::where('konsumen_id', $konsumen->id)->orderBy('id', 'desc')->first()->sisa ?? 0;
                $pesan .= "Grand Total Tagihan Konsumen: \n".
                'Rp. '.number_format($sisaTerakhir, 0, ',', '.')."\n\n";

                $pesan .= "==========================\n";

                $checkInvoice = InvoiceJual::where('konsumen_id', $konsumen->id)
                    ->where('titipan', 0)
                    ->where('lunas', 0)
                    ->where('void', 0)
                    ->whereBetween('jatuh_tempo', [Carbon::now(), Carbon::now()->addDays(7)])
                    ->get();

                if ($checkInvoice->count() > 0) {
                    $pesan .= "Tagihan jatuh tempo :\n\n";
                    foreach ($checkInvoice as $key => $value) {
                        $pesan .= 'No Invoice : '.$value->kode."\n".
                                    'Tgl jatuh tempo : '.Carbon::parse($value->jatuh_tempo)->translatedFormat('d-m-Y')."\n".
                                    'Nilai Tagihan  :  Rp '.number_format($value->grand_total - $value->dp - $value->dp_ppn, 0, ',', '.')."\n\n";
                    }
                }

            }

            // tambahkan warning jika ada tagihan sudah 7 hari sebelum jatuh tempo ( Nomor invoice, tanggal jatuh tempo, dan nilai tagihan)
            $pesan .= 'Terima kasih 🙏🙏🙏';

            $dbWa = new GroupWa;

            // $file = $pdfUrl;
            if (strlen($tujuan) > 10) {
                $wa = $dbWa->sendWa($tujuan, $pesan);
            }


            $invoice->update([
                'send_wa' => 1,
            ]);
        }

        return view('billing.stok.invoice',
            [
                'pdfUrl' => $pdfUrl,
            ]);
    }

    public function invoice_image(InvoiceJual $invoice)
    {
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');
        $pt = Config::where('untuk', $invoice->kas_ppn == 1 ? 'resmi' : 'non-resmi')->first();
        Carbon::setLocale('id');

        $jam = CarbonImmutable::parse($invoice->created_at)->translatedFormat('H:i');
        $tanggal = CarbonImmutable::parse($invoice->created_at)->translatedFormat('d F Y');

        // Render HTML view and store it as a string
        $html = view('billing.stok.invoice-pdf', [
            'data' => $invoice->load('konsumen', 'invoice_detail.stok.type', 'invoice_detail.stok.barang', 'invoice_detail.stok.unit', 'invoice_detail.stok.kategori', 'invoice_detail.stok.barang_nama'),
            'pt' => $pt,
            'jam' => $jam,
            'tanggal' => $tanggal,
        ])->render();

        $directory = storage_path('app/public/invoices');
        $jpegPath = $directory.'/invoice-'.$invoice->id.'.jpeg';

        // Check if the directory exists, if not, create it
        if (! file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        // Delete the existing JPEG if it exists
        if (file_exists($jpegPath)) {
            unlink($jpegPath);
        }

        // Save the new JPEG
        Browsershot::html($html)
            ->windowSize(707, 1000) // Ukuran A4 dalam portrait
            ->setOption('landscape', false) // Atur ke true jika ingin landscape
            ->save($jpegPath);

        // Generate the URL for the JPEG
        $jpegUrl = asset('storage/invoices/invoice-'.$invoice->id.'.jpeg');

        $konsumen = $invoice->konsumen ?? $invoice->konsumen_temp;

        if ($konsumen && $konsumen->no_hp) {
            $tujuan = str_replace('-', '', $konsumen->no_hp);
            $pesan = 'Terima kasih telah berbelanja di '.$pt->nama;
            $file = $jpegPath;
            $wa = new StarSender($tujuan, $pesan, $file);

            $wa->sendGroup();
        }

        return view('billing.stok.invoice', [
            'jpegUrl' => $jpegUrl,
        ]);
    }
}
