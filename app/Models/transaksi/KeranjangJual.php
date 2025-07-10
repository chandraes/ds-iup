<?php

namespace App\Models\transaksi;

use App\Models\db\Barang\Barang;
use App\Models\db\Barang\BarangStokHarga;
use App\Models\db\Karyawan;
use App\Models\db\Konsumen;
use App\Models\db\Pajak;
use App\Models\GroupWa;
use App\Models\KasBesar;
use App\Models\KasKonsumen;
use App\Models\Katalog;
use App\Models\KonsumenTemp;
use App\Models\Pajak\RekapPpn;
use App\Models\PpnKeluaran;
use App\Models\PpnMasukan;
use App\Models\Rekening;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class KeranjangJual extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $appends = ['nf_harga', 'nf_jumlah', 'nf_total'];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function stok()
    {
        return $this->belongsTo(BarangStokHarga::class, 'barang_stok_harga_id');
    }

    public function getNfHargaAttribute()
    {
        return number_format($this->harga_satuan, 0, ',', '.');
    }

    public function getNfJumlahAttribute()
    {
        return number_format($this->jumlah, 0, ',', '.');
    }

    public function getNfTotalAttribute()
    {
        return number_format($this->total, 0, ',', '.');
    }

    public function checkout($data)
    {
        try {
            DB::beginTransaction();

            // dd($data);
            $keranjang = $this->where('user_id', auth()->user()->id)->get();

            if ($keranjang->isEmpty()) {
                return [
                    'status' => 'error',
                    'message' => 'Keranjang masih kosong',
                ];
            }

            $dipungut = isset($data['dipungut']) ? $data['dipungut'] : 1;

            $barang_ppn = $keranjang->first()->barang_ppn;

            $dbPajak = new Pajak;
            $dbInvoice = new InvoiceJual;

            $data['total'] = $keranjang->sum('total');
            $data['diskon'] = isset($data['diskon']) ? str_replace('.', '', $data['diskon']) : 0;
            $data['add_fee'] = isset($data['add_fee']) ? str_replace('.', '', $data['add_fee']) : 0;

            $data['titipan'] = $data['pembayaran'] == 3 ? 1 : 0;

            $ppnVal = $dbPajak->where('untuk', 'ppn')->first()->persen;
            $dppSetelahDiskon = $data['total'] - $data['diskon'];

            $data['ppn'] = $keranjang->where('barang_ppn', 1)->first() ? ($dppSetelahDiskon * $ppnVal / 100) : 0;

            $data['kas_ppn'] = $barang_ppn;

            $data['nomor'] = $dbInvoice->generateNomor($barang_ppn);
            $data['kode'] = $dbInvoice->generateKode($barang_ppn);

            $data['dp'] = isset($data['dp']) ? str_replace('.', '', $data['dp']) : 0;

            $data['dp_ppn'] = isset($data['dp_ppn']) ? str_replace('.', '', $data['dp_ppn']) : 0;

            if ($dipungut == 1) {
                $data['grand_total'] = $dppSetelahDiskon + $data['ppn'] + $data['add_fee'];
            } else {
                $data['grand_total'] = $dppSetelahDiskon + $data['add_fee'];
            }

            $data['ppn_dipungut'] = $dipungut;

            if ($data['konsumen_id'] == '*') {
                $konsumen = KonsumenTemp::create([
                    'nama' => $data['nama'],
                    'no_hp' => isset($data['no_hp']) ? $data['no_hp'] : null,
                    'npwp' => isset($data['npwp']) ? $data['npwp'] : null,
                    'alamat' => isset($data['alamat']) ? $data['alamat'] : null,
                ]);
                unset($data['konsumen_id']);
                $data['konsumen_temp_id'] = $konsumen->id;
                $data['lunas'] = 1;
            } else {
                $konsumen = Konsumen::find($data['konsumen_id']);

                $data['lunas'] = $konsumen->pembayaran == 1 || $data['pembayaran'] == 1 ? 1 : 0;

                $data['karyawan_id'] = $konsumen->karyawan_id;

                // kalau sistem pembayaran konsumen adalah tempo dan sistem pembayaran invoice bukan tunai
                // maka cek sisa plafon konsumen
                if ($konsumen->pembayaran == 2 && $data['pembayaran'] != 1) {
                    $sisaTerakhir = KasKonsumen::where('konsumen_id', $konsumen->id)->orderBy('id', 'desc')->first()->sisa ?? 0;
                    if ($sisaTerakhir + $data['grand_total'] > $konsumen->plafon) {
                        return [
                            'status' => 'error',
                            'message' => 'Plafon konsumen sudah melebihi batas.',
                        ];
                    }

                    // jika invoice pembayaran adalah tempo, cek apakah konsumen memiliki tagihan yang jatuh tempo
                    if ($data['pembayaran'] == 2) {
                        $checkInvoice = InvoiceJual::where('konsumen_id', $konsumen->id)
                            ->where('titipan', 0)
                            ->where('lunas', 0)
                            ->where('void', 0)
                            ->where('jatuh_tempo', '<', today())
                            ->exists();

                        if ($checkInvoice) {
                            return [
                                'status' => 'error',
                                'message' => 'Konsumen memiliki tagihan yang telah jatuh tempo.',
                            ];
                        }
                    }

                    $data['jatuh_tempo'] = now()->addDays($konsumen->tempo_hari);

                }

            }

            $stateBayar = $data['lunas'] == 1 ? 1 : 0;
            $stateDP = 0;

            // Update sisa tagihan dan sisa ppn
            if ($data['lunas'] != 1) {
                $data['sisa_tagihan'] = $data['ppn_dipungut'] == 1 ? $data['grand_total'] - ($data['dp'] + $data['dp_ppn']) : $data['grand_total'] - $data['dp'];
                $data['sisa_ppn'] = $data['ppn'] - $data['dp_ppn'];
            }
            // Create Invoice
            $data['send_wa'] = 0;
            $data['sistem_pembayaran'] = $data['pembayaran'];

            $invoice = $dbInvoice->create($data);

            foreach ($keranjang as $item) {
                $invoice->invoice_detail()->create([
                    'barang_id' => $item->barang_id,
                    'barang_stok_harga_id' => $item->barang_stok_harga_id,
                    'jumlah' => $item->jumlah,
                    'harga_satuan' => $item->harga_satuan,
                    'total' => $item->total,
                ]);
            }

            if ($data['ppn'] > 0 && $stateBayar == 1) {
                $this->ppn_keluaran($invoice->id, $data['ppn'], $dipungut);
            }

            if ($data['dp_ppn'] > 0) {
                $this->ppn_keluaran($invoice->id, $data['dp_ppn'], $dipungut);
            }

            if ($invoice->lunas == 0 && $invoice->dp > 0) {
                $stateDP = 1;
            }

            $stateTempoWa = 0;

            if ($invoice->lunas == 0 && $invoice->dp == 0) {
                $stateTempoWa = 1;
            }

            $this->update_stok($keranjang);

            // kalau konsumen adalah konsumen tetap, maka update kas konsumen
            if (isset($data['konsumen_id'])) {
                $dbKasKonsumen = new KasKonsumen;
                $sisaTerakhirKonsumen = $dbKasKonsumen->sisaTerakhir($konsumen->id);
                $isPembayaranTunai = $konsumen->pembayaran == 1 || $data['pembayaran'] == 1 ? 1 : 0;

                if ($dipungut == 1) {
                    $sisaTerakhirKonsumen = $isPembayaranTunai
                    ? $sisaTerakhirKonsumen
                    : $sisaTerakhirKonsumen + $data['grand_total'] - ($data['dp'] + $data['dp_ppn']);
                } else {
                    $sisaTerakhirKonsumen = $isPembayaranTunai
                    ? $sisaTerakhirKonsumen
                    : $sisaTerakhirKonsumen + $data['grand_total'] - $data['dp'];
                }

                $sisa = $sisaTerakhirKonsumen < 0
                    ? 0
                    : $sisaTerakhirKonsumen;

                $uraianKasKonsumen = ($data['pembayaran'] == 1) ? 'Cash ' : (($data['pembayaran'] == 2) ? 'Tempo ' : 'Titipan ');
                $uraianKasKonsumen .= $invoice->kode;

                $variabel = ($data['pembayaran'] == 1) ? 'cash' : (($data['pembayaran'] == 2) ? 'hutang' : 'titipan');

                $grandTotalMinusDp = $data['grand_total'] - $data['dp'];
                $grandTotalMinusDpPpn = $data['grand_total'] - ($data['dp'] + $data['dp_ppn']);

                $dbKasKonsumen->create([
                    'konsumen_id' => $konsumen->id,
                    'invoice_jual_id' => $invoice->id,
                    'uraian' => $uraianKasKonsumen,
                    $variabel => $dipungut == 1 ? $grandTotalMinusDpPpn : $grandTotalMinusDp,
                    'sisa' => $sisa,
                ]);

            }

            $waState = 0;
            $dbKas = new KasBesar;

            if ($stateBayar == 1) {

                $ppn_kas = $data['ppn'] > 0 ? 1 : 0;
                $untukRekening = $ppn_kas == 1 ? 'kas-besar-ppn' : 'kas-besar-non-ppn';
                $rekening = Rekening::where('untuk', $untukRekening)->first();
                $pembayaran = 'Lunas';
                $uraian = 'Cash';
                $store = $dbKas->create([
                    'ppn_kas' => $ppn_kas,
                    'invoice_jual_id' => $invoice->id,
                    'nominal' => $data['grand_total'],
                    'uraian' => 'Cash Lunas',
                    'jenis' => 1,
                    'saldo' => $dbKas->saldoTerakhir($ppn_kas) + $data['grand_total'],
                    'no_rek' => $rekening->no_rek,
                    'nama_rek' => $rekening->nama_rek,
                    'bank' => $rekening->bank,
                    'modal_investor_terakhir' => $dbKas->modalInvestorTerakhir($ppn_kas),
                ]);

                $waState = 1;
            }

            if ($stateDP == 1) {
                $ppn_kas = $data['ppn'] > 0 ? 1 : 0;

                if ($dipungut == 1) {
                    $totalDP = $data['dp'] + $data['dp_ppn'];
                } else {
                    $totalDP = $data['dp'];
                }

                $untukRekening = $ppn_kas == 1 ? 'kas-besar-ppn' : 'kas-besar-non-ppn';
                $rekening = Rekening::where('untuk', $untukRekening)->first();
                $uraian = 'DP';
                $pembayaran = $konsumen->sistem_pembayaran.' '.$konsumen->tempo_hari.' Hari';
                $store = $dbKas->create([
                    'ppn_kas' => $ppn_kas,
                    'invoice_jual_id' => $invoice->id,
                    'nominal' => $totalDP,
                    'uraian' => $uraian.' '.$pembayaran,
                    'jenis' => 1,
                    'saldo' => $dbKas->saldoTerakhir($ppn_kas) + $totalDP,
                    'no_rek' => $rekening->no_rek,
                    'nama_rek' => $rekening->nama_rek,
                    'bank' => $rekening->bank,
                    'modal_investor_terakhir' => $dbKas->modalInvestorTerakhir($ppn_kas),
                ]);

                $waState = 1;
            }

            $this->where('user_id', auth()->user()->id)->delete();

            DB::commit();

            if ($waState == 1) {
                $addPesan = '';
                if ($konsumen->pembayaran == 2) {
                    $sisaTerakhir = KasKonsumen::where('konsumen_id', $konsumen->id)->orderBy('id', 'desc')->first()->sisa ?? 0;
                    $plafon = $konsumen->plafon;
                    $addPesan .= "Total Tagihan Konsumen: \n".
                                'Rp. '.number_format($sisaTerakhir, 0, ',', '.')."\n\n".
                                "Plafon Konsumen: \n".
                                'Rp. '.number_format($plafon, 0, ',', '.')."\n\n";
                }

                if ($barang_ppn == 1) {
                    $addPesan .= "Sisa Saldo Kas Besar PPN: \n".
                                'Rp. '.number_format($dbKas->saldoTerakhir(1), 0, ',', '.')."\n\n".
                                "Total Modal Investor PPN: \n".
                                'Rp. '.number_format($dbKas->modalInvestorTerakhir(1), 0, ',', '.')."\n\n";
                } else {
                    $addPesan .= "Sisa Saldo Kas Besar Non PPN: \n".
                                'Rp. '.number_format($dbKas->saldoTerakhir(0), 0, ',', '.')."\n\n".
                                "Total Modal Investor Non PPN: \n".
                                'Rp. '.number_format($dbKas->modalInvestorTerakhir(0), 0, ',', '.')."\n\n";
                }

                if ($invoice->konsumen_id) {
                    $checkInvoice = InvoiceJual::where('konsumen_id', $konsumen->id)
                        ->where('lunas', 0)
                        ->where('void', 0)
                        ->whereBetween('jatuh_tempo', [Carbon::now(), Carbon::now()->addDays(7)])
                        ->get();

                    if ($checkInvoice->count() > 0) {
                        $addPesan .= "==========================\n";
                        $addPesan .= "Tagihan jatuh tempo :\n\n";
                        foreach ($checkInvoice as $key => $value) {
                            $addPesan .= 'No Invoice : '.$value->kode."\n".
                                        'Tgl jatuh tempo : '.Carbon::parse($value->jatuh_tempo)->translatedFormat('d-m-Y')."\n".
                                        'Nilai Tagihan  :  Rp '.number_format($value->grand_total - $value->dp - $value->dp_ppn, 0, ',', '.')."\n\n";
                        }
                    }
                }

                $dbPPn = new PpnMasukan;
                $dbRekapPpn = new RekapPpn;
                $saldoTerakhirPpn = $dbRekapPpn->saldoTerakhir();
                $ppnMasukan = $dbPPn->where('is_finish', 0)->sum('nominal') + $saldoTerakhirPpn;
                $dbPpnKeluaran = new PpnKeluaran;
                $ppnKeluaran = $dbPpnKeluaran->where('is_expired', 0)->where('is_finish', 0)->sum('nominal');

                // dd($invoice->kode);

                $nama_konsumen = $invoice->konsumen_id ? $konsumen->kode_toko->kode.' '.$konsumen->nama : $konsumen->nama;

                $pesan = "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n".
                            "*FORM PENJUALAN*\n".
                            "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n\n".
                            "No Invoice:\n".
                            '*'.$invoice->kode."*\n\n".
                            'Uraian : *'.$uraian."*\n".
                            'Pembayaran : *'.$pembayaran."*\n\n".
                            'Konsumen : *'.$nama_konsumen."*\n".
                            'Nilai :  *Rp. '.number_format($store->nominal, 0, ',', '.')."*\n\n".
                            "Ditransfer ke rek:\n\n".
                            'Bank      : '.$store->bank."\n".
                            'Nama    : '.$store->nama_rek."\n".
                            'No. Rek : '.$store->no_rek."\n\n".
                            "==========================\n".
                            $addPesan.
                            "Total PPn Masukan : \n".
                            'Rp. '.number_format($ppnMasukan, 0, ',', '.')."\n\n".
                            "Total PPn Keluaran : \n".
                            'Rp. '.number_format($ppnKeluaran, 0, ',', '.')."\n\n".
                            "Terima kasih 🙏🙏🙏\n";

                $group = GroupWa::where('untuk', $untukRekening)->first()->nama_group;
                $dbKas->sendWa($group, $pesan);
            }

            if ($stateTempoWa == 1) {
                $addPesan = '';

                if ($konsumen->pembayaran == 2 && $data['pembayaran'] != 1) {
                    $sisaTerakhir = KasKonsumen::where('konsumen_id', $konsumen->id)->orderBy('id', 'desc')->first()->sisa ?? 0;
                    $plafon = $konsumen->plafon;

                    $pembayaran = $data['pembayaran'] == 2 ? $konsumen->sistem_pembayaran.' '.$konsumen->tempo_hari.' Hari' : 'Titipan';
                    // $pembayaran = $konsumen->sistem_pembayaran. ' '. $konsumen->tempo_hari. ' Hari';

                    $addPesan .= "Total Tagihan Konsumen: \n".
                                'Rp. '.number_format($sisaTerakhir, 0, ',', '.')."\n\n".
                                "Plafon Konsumen: \n".
                                'Rp. '.number_format($plafon, 0, ',', '.')."\n\n";
                }

                if ($barang_ppn == 1) {
                    $addPesan .= "Sisa Saldo Kas Besar PPN: \n".
                                'Rp. '.number_format($dbKas->saldoTerakhir(1), 0, ',', '.')."\n\n".
                                "Total Modal Investor PPN: \n".
                                'Rp. '.number_format($dbKas->modalInvestorTerakhir(1), 0, ',', '.')."\n\n";
                } else {
                    $addPesan .= "Sisa Saldo Kas Besar Non PPN: \n".
                                'Rp. '.number_format($dbKas->saldoTerakhir(0), 0, ',', '.')."\n\n".
                                "Total Modal Investor Non PPN: \n".
                                'Rp. '.number_format($dbKas->modalInvestorTerakhir(0), 0, ',', '.')."\n\n";
                }

                if ($invoice->konsumen_id) {
                    $checkInvoice = InvoiceJual::where('konsumen_id', $konsumen->id)
                        ->where('titipan', 0)
                        ->where('lunas', 0)
                        ->where('void', 0)
                        ->whereBetween('jatuh_tempo', [Carbon::now(), Carbon::now()->addDays(7)])
                        ->get();

                    if ($checkInvoice->count() > 0) {
                        $addPesan .= "==========================\n";
                        $addPesan .= "Tagihan jatuh tempo :\n\n";
                        foreach ($checkInvoice as $key => $value) {
                            $addPesan .= 'No Invoice : '.$value->kode."\n".
                                        'Tgl jatuh tempo : '.Carbon::parse($value->jatuh_tempo)->translatedFormat('d-m-Y')."\n".
                                        'Nilai Tagihan  :  Rp '.number_format($value->grand_total - $value->dp - $value->dp_ppn, 0, ',', '.')."\n\n";
                        }
                    }
                }

                // if ($barang_ppn == 1) {
                //     $rekening = Rekening::where('untuk', 'kas-besar-ppn')->first();
                // } else {
                //     $rekening = Rekening::where('untuk', 'kas-besar-non-ppn')->first();
                // }

                $dbPPn = new PpnMasukan;
                $dbRekapPpn = new RekapPpn;
                $saldoTerakhirPpn = $dbRekapPpn->saldoTerakhir();
                $ppnMasukan = $dbPPn->where('is_finish', 0)->sum('nominal') + $saldoTerakhirPpn;
                $dbPpnKeluaran = new PpnKeluaran;
                $ppnKeluaran = $dbPpnKeluaran->where('is_expired', 0)->where('is_finish', 0)->sum('nominal');
                $header = $data['pembayaran'] == 3 ? "🟢🟢🟢🟢🟢🟢🟢🟢🟢\n" : "🟡🟡🟡🟡🟡🟡🟡🟡🟡\n";

                $nama_konsumen = $invoice->konsumen_id ? $konsumen->kode_toko->kode.' '.$konsumen->nama : $konsumen->nama;

                $pesan = $header.
                               "*FORM PENJUALAN*\n".
                               $header."\n".
                               "No Invoice:\n".
                               '*'.$invoice->kode."*\n\n".
                               "Uraian : *Tanpa DP*\n".
                               'Pembayaran : *'.$pembayaran."*\n\n".
                               'Konsumen : *'.$nama_konsumen."*\n".
                               'Nilai :  *Rp. '.$invoice->nf_sisa_tagihan."*\n\n".
                               // "Ditransfer ke rek:\n\n".
                               // "Bank      : ".$rekening->bank."\n".
                               // "Nama    : ".$rekening->nama_rek."\n".
                               // "No. Rek : ".$rekening->no_rek."\n\n".
                               "==========================\n".
                               $addPesan.
                               "Total PPn Masukan : \n".
                               'Rp. '.number_format($ppnMasukan, 0, ',', '.')."\n\n".
                               "Total PPn Keluaran : \n".
                               'Rp. '.number_format($ppnKeluaran, 0, ',', '.')."\n\n".
                               "Terima kasih 🙏🙏🙏\n";

                $ppn_kas = $data['ppn'] > 0 ? 1 : 0;
                $untukRekening = $ppn_kas == 1 ? 'kas-besar-ppn' : 'kas-besar-non-ppn';

                $group = GroupWa::where('untuk', $untukRekening)->first()->nama_group;
                $dbKas->sendWa($group, $pesan);
            }

        } catch (\Throwable $th) {
            DB::rollBack();

            return [
                'status' => 'error',
                'message' => $th->getMessage(),
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Transaksi berhasil',
            'invoice' => $invoice,
        ];
    }

    private function generateInvoicePdf($invoice) {}

    private function update_stok($keranjang)
    {
        foreach ($keranjang as $item) {
            $barang = BarangStokHarga::find($item->barang_stok_harga_id);

            if ($barang->stok < $item->jumlah) {

                return [
                    'id' => $item->id,
                    'status' => false,
                ];
            }

            $barang->stok -= $item->jumlah;
            $barang->save();
        }

        return true;
    }

    private function ppn_keluaran($invoice_id, $ppn, $dipungut)
    {
        $db = new PpnKeluaran;

        $saldo = $db->saldoTerakhir();
        $invoice = InvoiceJual::find($invoice_id);

        $uraianPrefix = $invoice->lunas == 0 ? 'DP PPN ' : 'PPN ';
        $db->create([
            'invoice_jual_id' => $invoice_id,
            'uraian' => $uraianPrefix.$invoice->kode,
            'nominal' => $ppn,
            'saldo' => $saldo + $ppn,
            'dipungut' => $dipungut,
        ]);

        return true;
    }

    public function checkoutSales($data)
    {
        $stok_update = null;
        $status = null;

        $konsumen = Konsumen::find($data['konsumen_id']);

        $data['lunas'] = $konsumen->pembayaran == 1 || $data['pembayaran'] == 1 ? 1 : 0;

        // kalau sistem pembayaran konsumen adalah tempo dan sistem pembayaran invoice bukan tunai
        // maka cek sisa plafon konsumen
        if ($konsumen->pembayaran == 2 && $data['pembayaran'] != 1) {
            // $sisaTerakhir = KasKonsumen::where('konsumen_id', $konsumen->id)->orderBy('id', 'desc')->first()->sisa ?? 0;
            // if ($sisaTerakhir + $data['grand_total'] > $konsumen->plafon) {
            //     return [
            //         'status' => 'error',
            //         'message' => 'Plafon konsumen sudah melebihi batas.',
            //     ];
            // }

            // jika invoice pembayaran adalah tempo, cek apakah konsumen memiliki tagihan yang jatuh tempo
            if ($data['pembayaran'] == 2) {
                $checkInvoice = InvoiceJual::where('konsumen_id', $konsumen->id)
                    ->where('titipan', 0)
                    ->where('lunas', 0)
                    ->where('void', 0)
                    ->where('jatuh_tempo', '<', today())
                    ->exists();

                if ($checkInvoice) {
                    return [
                        'status' => 'error',
                        'message' => 'Tidak Bisa melanjutkan Proses karena Konsumen memiliki tagihan yang telah jatuh tempo.',
                    ];
                }
            }

            // $data['jatuh_tempo'] = now()->addDays($konsumen->tempo_hari);

        }

        try {
            DB::beginTransaction();

            // dd($data);
            $keranjang = $this->where('user_id', auth()->user()->id)->get();

            $keranjang_ppn = $keranjang->where('barang_ppn', 1);
            $keranjang_non_ppn = $keranjang->where('barang_ppn', 0);

            if ($keranjang->isEmpty()) {
                return [
                    'status' => 'error',
                    'message' => 'Keranjang masih kosong',
                ];
            }

            $data['karyawan_id'] = auth()->user()->karyawan_id;
            $data['titipan'] = $data['pembayaran'] == 3 ? 1 : 0;
            $data['sistem_pembayaran'] = $data['pembayaran'];

            if ($data['karyawan_id'] == null) {
                return [
                    'status' => 'error',
                    'message' => 'User belum memiliki karyawan id. Silahkan menghubungi Admin.',
                ];
            }

            $data['lunas'] = $konsumen->pembayaran == 1 || $data['pembayaran'] == 1 ? 1 : 0;

            if ($keranjang_ppn->count() > 0) {
                $store_ppn = $this->keranjangPpn($keranjang_ppn, $data);

                if (isset($store_ppn['status']) && $store_ppn['status'] == false) {
                     $stok_update = $store_ppn;
                    throw new \Exception('Terdapat stok yang kurang dari barang yang dijual');
                }
            }

            if ($keranjang_non_ppn->count() > 0) {
                $store_non_ppn = $this->keranjangNonPpn($keranjang_non_ppn, $data);

                if (isset($store_non_ppn['status']) && $store_non_ppn['status'] == false) {
                    $stok_update = $store_non_ppn;
                    throw new \Exception('Terdapat stok yang kurang dari barang yang dijual');
                }
            }

            $this->where('user_id', auth()->user()->id)->delete();

            $inden = KeranjangInden::where('user_id', auth()->user()->id)->get();
            $pesanInden = '';

            if ($inden->count() > 0) {
                $jumlah_inden = $inden->count();

                $createInden = OrderInden::create([
                    'karyawan_id' => auth()->user()->karyawan_id,
                    'konsumen_id' => $data['konsumen_id'],
                    'jumlah' => $jumlah_inden,
                    'is_finished' => 0,
                ]);

                foreach ($inden as $barangInden) {
                    $createInden->detail()->create([
                        'barang_id' => $barangInden->barang_id,
                        'jumlah' => $barangInden->jumlah,
                    ]);

                    $barangInden->delete();
                }


                $nInden = 1;
                $pesanInden .= "==========================\n";
                $pesanInden .= "*Pre order*: \n";

                foreach ($createInden->load('detail.barang.barang_nama', 'detail.barang.satuan')->detail as $d) {
                    $pesanInden .= $nInden++.'. '.$d->barang->barang_nama->nama." ".$d->barang->kode."\n".$d->barang->merk." "."....... ". $d->jumlah.' ('.$d->barang->satuan->nama.")\n\n";
                }
            }

            DB::commit();

            $dbWa = new GroupWa;
            $dbInvoice = new InvoiceJualSales;
            $grandTotal = 0;
            $dp = 0;
            $sisa = 0;
            $kota = $konsumen->kabupaten_kota ? $konsumen->kabupaten_kota->nama_wilayah : '';

            $pesan = "*".$konsumen->kode_toko->kode. ' '.$konsumen->nama."*\n".
                    $konsumen->alamat."\n".
                    $kota."\n\n";

            $tanggal = Carbon::now()->translatedFormat('d F Y');

            $pesan .= "*Order* : ".$tanggal."\n";

            if (isset($store_ppn['pesan'])) {
                $pesan .= $store_ppn['pesan'];
                $grandTotal += $store_ppn['grand_total'];
                $dp += $store_ppn['dp'];
                $sisa += $store_ppn['sisa_tagihan'];
            }

            if (isset($store_non_ppn['pesan'])) {
                $pesan .= $store_non_ppn['pesan'];
                $grandTotal += $store_non_ppn['grand_total'];
                $dp += $store_non_ppn['dp'];
                $sisa += $store_non_ppn['sisa_tagihan'];
            }

            if ($inden->count() > 0) {
                $pesan .= $pesanInden;
            }

            $pesan .= "==========================\n";

            $sales = Karyawan::find(auth()->user()->karyawan_id);

            switch ($data['pembayaran']) {
                case 1:
                    $pembayaran = 'Cash';
                    break;
                case 2:
                    $pembayaran = 'Tempo : '.$konsumen->tempo_hari.' Hari';
                    break;
                case 3:
                    $pembayaran = 'Titipan';
                    break;
                default:
                    $pembayaran = '';
            }

            // $pembayaran = $data['pembayaran'] == 1 ? 'Cash' :  $dbInvoice->pembayaran($data['sistem_pembayaran']).": ".$konsumen->tempo_hari . ' Hari';
            $pesan .= "Note: \n".
                    "•⁠ ".$pembayaran."\n".
                    "•⁠ Sales: *".$sales->nama."*\n"
                    ."•⁠ CP: *".$sales->no_hp."*\n";

            $pesan .= "•⁠ Order: *Rp. ".number_format($grandTotal, 0, ',','.')."*\n";

            if ($dp > 0) {
                $pesan .= "•⁠ DP: *Rp. ".number_format($dp, 0, ',','.')."*\n";
                $pesan .= "•⁠ Sisa Tagihan: *Rp. ".number_format($sisa, 0, ',','.')."*\n";
            }

            $pesan .= "\nNo Kantor: *0853-3939-3918* \n";

            // tambahkn katalog
            // $katalog = Katalog::get();

            // if ($katalog->count() > 0) {
            //     $pesan .= "Katalog: \n";
            //     foreach ($katalog as $item) {
            //         $pesan .= $item->nama."\n";
            //     }
            // }


            $tujuan = $dbWa->where('untuk', 'sales-order')->first()->nama_group;

            $dbWa->sendWa($tujuan, $pesan);

            $no_konsumen = $konsumen->no_hp;
            $no_konsumen = str_replace('-', '', $no_konsumen);

            // check length no hp
            if (strlen($no_konsumen) > 10) {
                $dbWa->sendWa($no_konsumen, $pesan);
            }

        } catch (\Throwable $th) {
            DB::rollBack();

            if (isset($stok_update['status']) && $stok_update['status'] == false) {
                KeranjangJual::where('id', $stok_update['id'])->update([
                    'stok_kurang' => 1,
                ]);
            }

            return [
                'status' => 'error',
                'message' => $th->getMessage(),
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Transaksi berhasil',
        ];
    }

    private function keranjangPpn($keranjang, $data)
    {
        $data['kas_ppn'] = 1;

        $dipungut = isset($data['dipungut']) ? $data['dipungut'] : 1;

        $dbPajak = new Pajak;

        $data['total'] = $keranjang->sum('total');
        $data['diskon'] = isset($data['diskon']) ? str_replace('.', '', $data['diskon']) : 0;
        $data['add_fee'] = isset($data['add_fee']) ? str_replace('.', '', $data['add_fee']) : 0;

        $ppnVal = $dbPajak->where('untuk', 'ppn')->first()->persen;
        $dppSetelahDiskon = $data['total'] - $data['diskon'];

        $data['ppn'] = floor($dppSetelahDiskon * $ppnVal / 100);

        $data['dp'] = isset($data['dp']) ? str_replace('.', '', $data['dp']) : 0;

        $data['dp_ppn'] = isset($data['dp_ppn']) ? str_replace('.', '', $data['dp_ppn']) : 0;

        if ($dipungut == 1) {
            $data['grand_total'] = $dppSetelahDiskon + $data['ppn'] + $data['add_fee'];
        } else {
            $data['grand_total'] = $dppSetelahDiskon + $data['add_fee'];
        }

        $data['ppn_dipungut'] = $dipungut;

         // Update sisa tagihan dan sisa ppn
        if ($data['lunas'] != 1) {
            $data['sisa_tagihan'] = $data['ppn_dipungut'] == 1 ? $data['grand_total'] - ($data['dp'] + $data['dp_ppn']) : $data['grand_total'] - $data['dp'];
            $data['sisa_ppn'] = $data['ppn'] - $data['dp_ppn'];
        } else {
            $data['sisa_tagihan'] = 0;
            $data['sisa_ppn'] = 0;
        }
        // Create Invoice

        $dbInvoice = new InvoiceJualSales;
        $invoice = $dbInvoice->create($data);

        foreach ($keranjang as $item) {
            $invoice->invoice_detail()->create([
                'barang_id' => $item->barang_id,
                'barang_stok_harga_id' => $item->barang_stok_harga_id,
                'jumlah' => $item->jumlah,
                'harga_satuan' => $item->harga_satuan,
                'total' => $item->total,
            ]);
        }

        $stok_update = $this->update_stok($keranjang);

        if (isset($stok_update['status']) && $stok_update['status'] == false) {
            return [
                'id' => $stok_update['id'],
                'status' => false,
            ];
        }

        $pesan = "Barang A: \n";

        $n = 1;
        foreach ($invoice->load('invoice_detail.barang.barang_nama', 'invoice_detail.barang.satuan')->invoice_detail as $d) {
             $pesan .= $n++.'. '.$d->barang->barang_nama->nama." ".$d->barang->kode.""."\n".$d->barang->merk." "."....... ". $d->jumlah.' ('.$d->barang->satuan->nama.")\n\n";
        }

        return [
            'status' => true,
            'pesan' => $pesan,
            'grand_total' => $data['grand_total'],
            'dp' => $data['dp']+$data['dp_ppn'],
            'sisa_tagihan' => $data['sisa_tagihan'],
        ];

    }

    private function keranjangNonPpn($keranjang, $data)
    {
        $data['kas_ppn'] = 0;

        $dipungut = 1;

        $data['total'] = $keranjang->sum('total');
        $data['diskon'] = isset($data['diskon_non_ppn']) ? str_replace('.', '', $data['diskon_non_ppn']) : 0;
        $data['add_fee'] = isset($data['add_fee_non_ppn']) ? str_replace('.', '', $data['add_fee_non_ppn']) : 0;

        $dppSetelahDiskon = $data['total'] - $data['diskon'];

        $data['dp'] = isset($data['dp_non_ppn']) ? str_replace('.', '', $data['dp_non_ppn']) : 0;
        $data['dp_ppn'] = 0;

        $data['grand_total'] = $dppSetelahDiskon + $data['add_fee'];
        $data['ppn_dipungut'] = $dipungut;

        if ($data['lunas'] != 1) {
            // Update sisa tagihan dan sisa ppn
            $data['sisa_tagihan'] = $data['ppn_dipungut'] == 1 ? $data['grand_total'] - ($data['dp'] + $data['dp_ppn']) : $data['grand_total'] - $data['dp'];
            $data['sisa_ppn'] = 0;
        } else {
            // kalau sudah lunas, maka sisa tagihan dan sisa ppn adalah 0
            $data['sisa_tagihan'] = 0;
            $data['sisa_ppn'] = 0;
            # code...
        }
        $data['sisa_tagihan'] = $data['ppn_dipungut'] == 1 ? $data['grand_total'] - ($data['dp'] + $data['dp_ppn']) : $data['grand_total'] - $data['dp'];
        $data['sisa_ppn'] = 0;

        $dbInvoice = new InvoiceJualSales;
        $invoice = $dbInvoice->create($data);

        foreach ($keranjang as $item) {
            $invoice->invoice_detail()->create([
                'barang_id' => $item->barang_id,
                'barang_stok_harga_id' => $item->barang_stok_harga_id,
                'jumlah' => $item->jumlah,
                'harga_satuan' => $item->harga_satuan,
                'total' => $item->total,
            ]);
        }

        $stok_update = $this->update_stok($keranjang);

        if (isset($stok_update['status']) && $stok_update['status'] == false) {
            return [
                'id' => $stok_update['id'],
                'status' => false,
            ];
        }

        $pesan = "Barang B: \n";

        $n = 1;
        foreach ($invoice->load('invoice_detail.barang.barang_nama', 'invoice_detail.barang.satuan')->invoice_detail as $d) {
            $pesan .= $n++.'. '.$d->barang->barang_nama->nama." ".$d->barang->kode.""."\n".$d->barang->merk." "."....... ". $d->jumlah.' ('.$d->barang->satuan->nama.")\n\n";
        }

        return [
            'status' => true,
            'pesan' => $pesan,
            'grand_total' => $data['grand_total'],
            'dp' => $data['dp'],
            'sisa_tagihan' => $data['sisa_tagihan'],
        ];

    }

}
