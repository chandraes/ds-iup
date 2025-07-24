<?php

namespace App\Models\transaksi;

use App\Models\Config;
use App\Models\db\Barang\BarangStokHarga;
use App\Models\db\Karyawan;
use App\Models\db\Konsumen;
use App\Models\db\Pajak;
use App\Models\GroupWa;
use App\Models\KasBesar;
use App\Models\KasKonsumen;
use App\Models\KonsumenTemp;
use App\Models\Pajak\RekapPpn;
use App\Models\PpnKeluaran;
use App\Models\PpnMasukan;
use App\Models\Rekening;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InvoiceJual extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $appends = ['tanggal', 'id_jatuh_tempo', 'dpp', 'nf_ppn',
        'nf_grand_total', 'nf_dp', 'nf_dp_ppn', 'nf_sisa_ppn',
        'nf_sisa_tagihan',  'dpp_setelah_diskon', 'sistem_pembayaran_word', 'tanggal_en',
    ];

    public function invoice_jual_cicil()
    {
        return $this->hasMany(InvoiceJualCicil::class);
    }

    public function dataTahun()
    {
        return $this->selectRaw('YEAR(created_at) as tahun')
            ->groupBy('tahun')
            ->orderBy('tahun', 'desc')
            ->get();
    }

    public function invoiceByMonth($bulan, $tahun, $kas_ppn)
    {
        return $this->whereMonth('created_at', $bulan)
            ->where('kas_ppn', $kas_ppn)
            ->where('void', 0)
            ->where('lunas', 1)
            ->whereYear('created_at', $tahun)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getTanggalAttribute()
    {
        return Carbon::parse($this->created_at)->format('d-m-Y');
    }

    public function getTanggalEnAttribute()
    {
        return Carbon::parse($this->created_at)->format('Y-m-d');
    }

    public function getFullKodeAttribute()
    {
        return str_pad($this->nomor, 3, '0', STR_PAD_LEFT).'/'.Carbon::parse($this->created_at)->format('Y');
    }

    public function getIdJatuhTempoAttribute()
    {
        return Carbon::parse($this->jatuh_tempo)->format('d-m-Y');
    }

    public function getDppAttribute()
    {
        return number_format($this->total, 0, ',', '.');
    }

    public function getDppSetelahDiskonAttribute()
    {
        return number_format($this->total - $this->diskon, 0, ',', '.') ?? 0;
    }

    public function getNfPpnAttribute()
    {
        return number_format($this->ppn, 0, ',', '.');
    }

    public function getNfAddFeeAttribute()
    {
        return number_format($this->add_fee, 0, ',', '.');
    }

    public function getNfDiskonAttribute()
    {
        return number_format($this->diskon, 0, ',', '.');
    }

    public function getNfGrandTotalAttribute()
    {
        return number_format($this->grand_total, 0, ',', '.');
    }

    public function getNfDpAttribute()
    {
        return number_format($this->dp, 0, ',', '.') ?? 0;
    }

    public function getNfDpPpnAttribute()
    {
        return number_format($this->dp_ppn, 0, ',', '.') ?? 0;
    }

    public function getNfSisaPpnAttribute()
    {
        return number_format($this->sisa_ppn, 0, ',', '.');
    }

    public function getNfSisaTagihanAttribute()
    {
        return number_format($this->sisa_tagihan, 0, ',', '.');
    }

    public function getSistemPembayaranWordAttribute()
    {
        return match ($this->sistem_pembayaran) {
            1 => 'Cash',
            2 => 'Tempo',
            3 => 'Titipan',
            default => '-',
        };
    }

    public function generateNomor($barang_ppn)
    {
        return $this->where('kas_ppn', $barang_ppn)->whereYear('created_at', date('Y'))->max('nomor') + 1;
    }

    public function generateKode($barang_ppn)
    {
        $untuk = $barang_ppn == 1 ? 'resmi' : 'non-resmi';
        $app = Config::where('untuk', $untuk)->first();
        $singkatan = $app->singkatan;
        $nomor = $this->generateNomor($barang_ppn);
        $kode = $barang_ppn == 1 ? str_pad($nomor, 3, '0', STR_PAD_LEFT).'/'.$singkatan.'-INV/'.date('m/Y') : str_pad($nomor, 3, '0', STR_PAD_LEFT).'/'.$singkatan.'/'.date('m/Y');

        return $kode;

    }

    public function konsumen()
    {
        return $this->belongsTo(Konsumen::class);
    }

    public function konsumen_temp()
    {
        return $this->belongsTo(KonsumenTemp::class);
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class)->withDefault(
            ['nama' => '-']
        );
    }

    public function invoice_detail()
    {
        return $this->hasMany(InvoiceJualDetail::class);
    }

    public function bayar($id)
    {
        $kas = new KasBesar;
        $inv = $this->find($id);

        if ($inv->lunas == 1) {
            return [
                'status' => 'error',
                'message' => 'Invoice sudah lunas!!',
            ];
        }

        $kas_ppn = $inv->ppn > 0 ? 1 : 0;
        $kasMana = $kas_ppn == 1 ? 'kas-besar-ppn' : 'kas-besar-non-ppn';

        try {
            DB::beginTransaction();

            if ($kas_ppn == 1) {
                $this->store_ppn($id, $inv->sisa_ppn);
            }

            $sisa_tagihan = str_replace('.', '', $inv->sisa_tagihan);
            $rekening = Rekening::where('untuk', $kasMana)->first();

            $store = $kas->create([
                'invoice_jual_id' => $inv->id,
                'ppn_kas' => $kas_ppn,
                'uraian' => 'Pelunasan '.$inv->kode,
                'jenis' => '1',
                'nominal' => $sisa_tagihan,
                'saldo' => $kas->saldoTerakhir($kas_ppn) + $sisa_tagihan,
                'nama_rek' => $rekening->nama_rek,
                'no_rek' => $rekening->no_rek,
                'bank' => $rekening->bank,
                'modal_investor_terakhir' => $kas->modalInvestorTerakhir($kas_ppn),
            ]);

            // update kas konsumen
            $kas_konsumen = new KasKonsumen;
            $sisaKasKonsumen = $kas_konsumen->sisaTerakhir($inv->konsumen_id);
            $sisaAkhirKonsumen = $sisaKasKonsumen - $sisa_tagihan;

            $kas_konsumen->create([
                'konsumen_id' => $inv->konsumen_id,
                'invoice_jual_id' => $inv->id,
                'uraian' => 'Pelunasan '.$inv->kode,
                'bayar' => $sisa_tagihan,
                'sisa' => $sisaAkhirKonsumen < 0 ? 0 : $sisaAkhirKonsumen,
            ]);

            $inv->update([
                'lunas' => 1,
            ]);

            DB::commit();

            $getKas = $kas->getKas();

            $dbPPn = new PpnMasukan;
            $dbRekapPpn = new RekapPpn;
            $saldoTerakhirPpn = $dbRekapPpn->saldoTerakhir();
            $ppnMasukan = $dbPPn->where('is_finish', 0)->sum('nominal') + $saldoTerakhirPpn;
            $dbPpnKeluaran = new PpnKeluaran;
            $ppnKeluaran = $dbPpnKeluaran->where('is_expired', 0)->where('is_finish', 0)->sum('nominal');

            if ($kas_ppn == 1) {
                $addPesan = "Sisa Saldo Kas Besar: \n".
                            'Rp. '.number_format($getKas['saldo_ppn'], 0, ',', '.')."\n\n".
                            "Total Modal Investor PPN: \n".
                            'Rp. '.number_format($kas->modalInvestorTerakhir(1), 0, ',', '.')."\n\n";
            } else {
                $addPesan = "Sisa Saldo Kas Besar: \n".
                            'Rp. '.number_format($getKas['saldo_non_ppn'], 0, ',', '.')."\n\n".
                            "Total Modal Investor Non PPN: \n".
                            'Rp. '.number_format($getKas['modal_investor_non_ppn'], 0, ',', '.')."\n\n";
            }
            //  dd($kasMana);
            $pesan = "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n".
                        "*PELUNASAN JUAL BARANG*\n".
                        "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n\n".
                        'Uraian :  *'.$store->uraian."*\n\n".
                        'Nilai    :  *Rp. '.number_format($store->nominal, 0, ',', '.')."*\n\n".
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

            $group = GroupWa::where('untuk', $kasMana)->first()->nama_group;
            //  dd($group);
            $kas->sendWa($group, $pesan);

            return [
                'status' => 'success',
                'message' => 'Berhasil melunasi invoice!!',
            ];

        } catch (\Throwable $th) {
            DB::rollBack();

            return ['status' => 'error', 'message' => $th->getMessage()];
        }
    }

    public function store_ppn($inv_id, $ppn)
    {
        $db = new PpnKeluaran;
        $inv = $this->find($inv_id);
        $saldo = $db->saldoTerakhir() + $ppn;

        $db->create([
            'invoice_jual_id' => $inv_id,
            'nominal' => $ppn,
            'saldo' => $saldo,
            'uraian' => 'Pelunasan '.$inv->kode,
            'dipungut' => $inv->ppn_dipungut,
        ]);

        return true;

    }

    public function store_ppn_cicil($inv_id, $kode, $nominal, $dipungut)
    {
        $db = new PpnKeluaran;
        $saldo = $db->saldoTerakhir() + $nominal;

        $db->create([
            'invoice_jual_id' => $inv_id,
            'nominal' => $nominal,
            'saldo' => $saldo,
            'uraian' => 'Cicilan '.$kode,
            'dipungut' => $dipungut,
        ]);

        return true;

    }

    public function void($id)
    {
        $data = $this->find($id);

        $detail = $data->invoice_detail;

        if ($data->void == 1) {
            return [
                'status' => 'error',
                'message' => 'Invoice sudah di-void!!',
            ];
        }

        $dp = $data->ppn_dipungut == 1 ? $data->dp + $data->dp_ppn : $data->dp;
        $stateWa = 0;

        try {
            DB::beginTransaction();

            if ($dp > 0) {
                $dbKas = new KasBesar;
                $saldoKasBesar = $dbKas->saldoTerakhir($data->kas_ppn);

                if ($saldoKasBesar < $dp) {
                    return [
                        'status' => 'error',
                        'message' => 'Saldo kas besar tidak mencukupi!!. Saldo saat ini: Rp. '.number_format($saldoKasBesar, 0, ',', '.'),
                    ];
                }

                $this->pengembalianDp($id);
            } else {
                $stateWa = 1;
            }

            // update stok
            foreach ($detail as $d) {
                $stok = BarangStokHarga::find($d->barang_stok_harga_id);
                $stok->update([
                    'stok' => $stok->stok + $d->jumlah,
                ]);
            }

            // update kas konsumen
            if ($data->konsumen_id) {
                $kas_konsumen = new KasKonsumen;
                $sisaKasKonsumen = $kas_konsumen->sisaTerakhir($data->konsumen_id);
                $sisaAkhirKonsumen = $sisaKasKonsumen - $data->grand_total;

                if ($sisaAkhirKonsumen < 0) {
                    $sisaAkhirKonsumen = 0;
                }

                $kas_konsumen->create([
                    'konsumen_id' => $data->konsumen_id,
                    'invoice_jual_id' => $data->id,
                    'uraian' => 'Void '.$data->kode,
                    'bayar' => $data->grand_total,
                    'sisa' => $sisaAkhirKonsumen,
                ]);
            }

            $data->update([
                'void' => 1,
            ]);

            DB::commit();

            if ($stateWa == 1) {
                $gw = new GroupWa;
                $uraian = 'Void '.$data->kode;
                $rek = [
                    'nama_rek' => '-',
                    'no_rek' => '-',
                    'bank' => '-',
                ];
                $nominal = 0;

                $pesan = $gw->generateMessage(0, 'Void Penjualan', $data->kas_ppn, $uraian, $nominal, $rek);

                $group = $gw->where('untuk', $data->kas_ppn == 1 ? 'kas-besar-ppn' : 'kas-besar-non-ppn')->first()->nama_group;

                $gw->sendWa($group, $pesan);
            }

            return [
                'status' => 'success',
                'message' => 'Berhasil void invoice!!',
            ];

        } catch (\Throwable $th) {

            DB::rollBack();

            return [
                'status' => 'error',
                'message' => 'Terjadi Kesalahan! '.$th->getMessage(),
            ];
        }

    }

    private function pengembalianDp($id)
    {
        $data = $this->find($id);

        $totalDp = $data->ppn_dipungut == 1 ? $data->dp + $data->dp_ppn : $data->dp;

        $kas = new KasBesar;
        $rekening = Rekening::where('untuk', $data->kas_ppn == 1 ? 'kas-besar-ppn' : 'kas-besar-non-ppn')->first();

        $rek = [
            'nama_rek' => $rekening->nama_rek,
            'no_rek' => $rekening->no_rek,
            'bank' => $rekening->bank,
        ];

        $store = $kas->create([
            'ppn_kas' => $data->kas_ppn,
            'invoice_jual_id' => $data->id,
            'uraian' => 'Void '.$data->kode,
            'jenis' => 0,
            'nominal' => $totalDp,
            'saldo' => $kas->saldoTerakhir($data->kas_ppn) - $totalDp,
            'nama_rek' => $rekening->nama_rek,
            'no_rek' => $rekening->no_rek,
            'bank' => $rekening->bank,
            'modal_investor_terakhir' => $kas->modalInvestorTerakhir($data->kas_ppn),
        ]);

        $deletePpn = PpnKeluaran::where('invoice_jual_id', $data->id)->delete();

        $pesan = $kas->generateMessage(0, 'Void Penjualan', $data->kas_ppn, $store->uraian, $store->nominal, $rek);

        $group = GroupWa::where('untuk', $data->kas_ppn == 1 ? 'kas-besar-ppn' : 'kas-besar-non-ppn')->first()->nama_group;

        $kas->sendWa($group, $pesan);

        return true;

    }

    public function cicil($data)
    {
        $apa_ppn = $data['apa_ppn'];

        $data['nominal'] = str_replace('.', '', $data['nominal']);
        $data['ppn'] = isset($data['ppn']) ? str_replace('.', '', $data['ppn']) : 0;

        $totalCicil = $data['nominal'] + $data['ppn'];

        unset($data['apa_ppn']);
        $inv = $this->find($data['invoice_jual_id']);

        if ($inv->konsumen_id == null) {
            return [
                'status' => 'error',
                'message' => 'Invoice ini tidak memiliki konsumen!!',
            ];
        }

        $kas = new KasBesar;

        $kas_ppn = $inv->ppn > 0 ? 1 : 0;
        $kasMana = $kas_ppn == 1 ? 'kas-besar-ppn' : 'kas-besar-non-ppn';

        try {
            DB::beginTransaction();

            if ($kas_ppn == 1) {
                $this->store_ppn_cicil($inv->id, $inv->kode, $data['ppn'], $inv->ppn_dipungut);
            }

            $rekening = Rekening::where('untuk', $kasMana)->first();

            $store = $kas->create([
                'invoice_jual_id' => $inv->id,
                'ppn_kas' => $kas_ppn,
                'uraian' => 'Cicilan '.$inv->kode,
                'jenis' => '1',
                'nominal' => $totalCicil,
                'saldo' => $kas->saldoTerakhir($kas_ppn) + $totalCicil,
                'nama_rek' => $rekening->nama_rek,
                'no_rek' => $rekening->no_rek,
                'bank' => $rekening->bank,
                'modal_investor_terakhir' => $kas->modalInvestorTerakhir($kas_ppn),
            ]);

            // update kas konsumen
            $kas_konsumen = new KasKonsumen;
            $sisaKasKonsumen = $kas_konsumen->sisaTerakhir($inv->konsumen_id);
            $sisaAkhirKonsumen = $sisaKasKonsumen - $totalCicil;

            $kas_konsumen->create([
                'konsumen_id' => $inv->konsumen_id,
                'invoice_jual_id' => $inv->id,
                'uraian' => 'Cicilan '.$inv->kode,
                'bayar' => $totalCicil,
                'sisa' => $sisaAkhirKonsumen < 0 ? 0 : $sisaAkhirKonsumen,
            ]);

            $inv->update([
                'sisa_tagihan' => $inv->sisa_tagihan - $totalCicil,
                'sisa_ppn' => $inv->sisa_ppn - $data['ppn'],
            ]);

            InvoiceJualCicil::create([
                'invoice_jual_id' => $inv->id,
                'nominal' => $data['nominal'],
                'ppn' => $data['ppn'],
            ]);

            DB::commit();

            $dbPPn = new PpnMasukan;
            $dbRekapPpn = new RekapPpn;
            $saldoTerakhirPpn = $dbRekapPpn->saldoTerakhir();
            $ppnMasukan = $dbPPn->where('is_finish', 0)->sum('nominal') + $saldoTerakhirPpn;
            $dbPpnKeluaran = new PpnKeluaran;
            $ppnKeluaran = $dbPpnKeluaran->where('is_expired', 0)->where('is_finish', 0)->sum('nominal');

            $addMessageUp = "Sisa Cicilan konsumen : \n".
                            'Rp. '.number_format($inv->sisa_tagihan, 0, ',', '.')."\n\n";

            $addMessage = "Total PPn Masukan : \n".
                            'Rp. '.number_format($ppnMasukan, 0, ',', '.')."\n\n".
                            "Total PPn Keluaran : \n".
                            'Rp. '.number_format($ppnKeluaran, 0, ',', '.')."\n\n";

            $dbWa = new GroupWa;

            $pesan = $dbWa->generateMessage(1, 'CICILAN JUAL BARANG', $kas_ppn, $store->uraian, $store->nominal, $rekening, $addMessage, $addMessageUp);

            $group = $dbWa->where('untuk', $kasMana)->first()->nama_group;
            //  dd($group);
            $dbWa->sendWa($group, $pesan);

            return [
                'status' => 'success',
                'message' => 'Berhasil melakukan cicilan invoice!!',
            ];

        } catch (\Throwable $th) {
            DB::rollBack();

            return ['status' => 'error', 'message' => $th->getMessage()];
        }
    }

    public function scopeBilling($query, $filters, $kas_ppn, $titipan)
    {
        $data = $query->with(['konsumen', 'invoice_jual_cicil'])
            ->where('void', 0)
            ->where('titipan', $titipan)
            ->where('lunas', 0)
            ->where('kas_ppn', $kas_ppn);

        if (isset($filters['expired']) && $filters['expired'] != '') {
            $data->where('jatuh_tempo', $filters['expired'] == 0 ? '>' : '<=', Carbon::now());
        }

        $data = $data->get();

        return $data;

    }

    public function scopeGabung($query, $filters)
    {
        $data = $query->with(['konsumen.kode_toko', 'konsumen.kecamatan', 'konsumen.kabupaten_kota','invoice_jual_cicil', 'karyawan'])
            ->where('void', 0)
            ->where('titipan', 0)
            ->where('lunas', 0);

        if (isset($filters['expired']) && $filters['expired'] != '') {
            $data->where('jatuh_tempo', $filters['expired'] == 'no' ? '>' : '<=', Carbon::now());
        }

        if (isset($filters['konsumen_id']) && $filters['konsumen_id'] != ''){
            $data->where('konsumen_id', $filters['konsumen_id']);
        }

        if (isset($filters['karyawan_id']) && $filters['karyawan_id'] != ''){
            $data->where('karyawan_id', $filters['karyawan_id']);
        }

        if (isset($filters['kecamatan_id']) && $filters['kecamatan_id'] != '') {
            $data->whereHas('konsumen', function ($query) use ($filters) {
                $query->where('kecamatan_id', $filters['kecamatan_id']);
            });
        }

        if(isset($filters['kabupaten_id']) && $filters['kabupaten_id'] != ''){
            $data->whereHas('konsumen', function ($query) use ($filters) {
                $query->where('kabupaten_kota_id', $filters['kabupaten_id']);
            });
        }

        if(isset($filters['apa_ppn']) && $filters['apa_ppn'] != ''){
            $ppn = $filters['apa_ppn'] == 'yes' ? 1 : 0;
            $data->where('kas_ppn', $ppn);
        }

        $data = $data->get();

        return $data;
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

    public function lanjut_order($id)
    {
        $invoiceSales = InvoiceJualSales::find($id);

        try {
            DB::beginTransaction();


            $dipungut = $invoiceSales->ppn_dipungut;

            $barang_ppn = $invoiceSales->kas_ppn;

            $dbPajak = new Pajak;

            $data['total'] = $invoiceSales->total;
            $data['diskon'] = $invoiceSales->diskon;
            $data['add_fee'] = $invoiceSales->add_fee;

            $data['titipan'] = $invoiceSales->sistem_pembayaran == 3 ? 1 : 0;
            $data['pembayaran'] = $invoiceSales->sistem_pembayaran;

            $ppnVal = $dbPajak->where('untuk', 'ppn')->first()->persen;
            $dppSetelahDiskon = $data['total'] - $data['diskon'];

            $data['ppn'] = $invoiceSales->ppn;

            $data['kas_ppn'] = $barang_ppn;

            $data['nomor'] = $this->generateNomor($barang_ppn);
            $data['kode'] = $this->generateKode($barang_ppn);

            $data['dp'] = $invoiceSales->dp;

            $data['dp_ppn'] = $invoiceSales->dp_ppn;

            $data['grand_total'] = $invoiceSales->grand_total;

            $data['ppn_dipungut'] = $dipungut;

            $data['konsumen_id'] = $invoiceSales->konsumen_id;
            $data['karyawan_id'] = $invoiceSales->karyawan_id;

            $konsumen = Konsumen::find($data['konsumen_id']);

            $data['lunas'] = $konsumen->pembayaran == 1 || $data['pembayaran'] == 1 ? 1 : 0;

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

            $invoice = $this->create($data);

            foreach ($invoiceSales->invoice_detail as $item) {
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

            $invoiceSales->update([
                'is_finished' => 1,
            ]);

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

                $pesan = "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n".
                            "*FORM PENJUALAN*\n".
                            "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n\n".
                            "No Invoice:\n".
                            '*'.$invoice->kode."*\n\n".
                            'Uraian : *'.$uraian."*\n".
                            'Pembayaran : *'.$pembayaran."*\n\n".
                            'Konsumen : *'.$konsumen->kode_toko->kode.' '.$konsumen->nama."*\n".
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

                $pesan = $header.
                               "*FORM PENJUALAN*\n".
                               $header."\n".
                               "No Invoice:\n".
                               '*'.$invoice->kode."*\n\n".
                               "Uraian : *Tanpa DP*\n".
                               'Pembayaran : *'.$pembayaran."*\n\n".
                               'Konsumen : *'.$konsumen->kode_toko->kode.' '.$konsumen->nama."*\n".
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

    public function omset_harian($month, $year, $karyawan_id = null)
    {
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        // Ambil semua karyawan dengan jabatan_id = 3
        if ($karyawan_id) {
            $karyawans = Karyawan::where('jabatan_id', 3)->where('id', $karyawan_id)->get();
        } else {
            $karyawans = Karyawan::where('jabatan_id', 3)->get();
        }

        // Buat array tanggal
        $dates = collect();


        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dates->push($date->format('Y-m-d'));
        }
        // dd($dates, $month, $year);
        // Ambil semua invoice dan pastikan 'tanggal' dalam format string Y-m-d
        $invoices = $this
            ->selectRaw('DATE(created_at) as tanggal_raw, karyawan_id, SUM(grand_total) as total')
            ->where('titipan', 0)
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->whereNotNull('karyawan_id')
            ->groupBy('tanggal_raw', 'karyawan_id')
            ->get()
            ->map(function($item) {
                $item->tanggal = Carbon::parse($item->tanggal_raw)->format('Y-m-d');
                return $item;
            });

        $invoice_void = $this->where('void', 1)
            ->selectRaw('DATE(updated_at) as tanggal_raw, karyawan_id, SUM(grand_total) as total')
            ->where('titipan', 0)
            ->whereMonth('updated_at', $month)
            ->whereYear('updated_at', $year)
            ->whereNotNull('karyawan_id')
            ->groupBy('tanggal_raw', 'karyawan_id')
            ->get()
            ->map(function($item) {
                $item->tanggal = Carbon::parse($item->tanggal_raw)->format('Y-m-d');
                return $item;
            });
        // Buat array hasil [tanggal][karyawan_id] = total
        $result = [];

        foreach ($dates as $date) {
            $row = ['tanggal' => $date];

            foreach ($karyawans as $karyawan) {
                $found = $invoices->first(function ($item) use ($date, $karyawan) {
                    return $item->tanggal_raw === $date && $item->karyawan_id == $karyawan->id;
                });

                $found_void = $invoice_void->first(function ($item) use ($date, $karyawan) {
                    return $item->tanggal_raw === $date && $item->karyawan_id == $karyawan->id;
                });

                $row[$karyawan->id] = $found ? $found->total : 0;

                $row[$karyawan->id] = $found_void ? $row[$karyawan->id] - $found_void->total : $row[$karyawan->id];
            }
            $result[] = $row;
        }

        return [
            'data' => $result,
            'karyawans' => $karyawans,
        ];
    }

    public function omset_harian_detail($tanggal, $karyawan_id)
    {
        $tanggal = Carbon::parse($tanggal)->format('Y-m-d');

        $data = $this->with(['invoice_detail', 'konsumen.kode_toko', 'konsumen.kabupaten_kota', 'konsumen.kecamatan'])
            ->where('titipan', 0)
            ->where('karyawan_id', $karyawan_id)
            ->whereDate('created_at', $tanggal)
            ->get();

        $void = $this->with(['invoice_detail', 'konsumen.kode_toko','konsumen.kabupaten_kota', 'konsumen.kecamatan'])
            ->where('titipan', 0)
            ->where('void', 1)
            ->where('karyawan_id', $karyawan_id)
            ->whereDate('updated_at', $tanggal)
            ->get();

        return [
            'data' => $data,
            'void' => $void
        ];
    }

    public function profit_harian($month, $year, $konsumen = null)
    {
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

         $dates = collect();


        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dates->push($date->format('Y-m-d'));
        }
        // dd($dates, $month, $year);
        // Ambil semua invoice dan pastikan 'tanggal' dalam format string Y-m-d
        $invoices = $this->select('id','created_at', 'total', 'konsumen_id', 'kode', 'konsumen_temp_id', 'diskon', 'add_fee')
                        ->with(['konsumen.kode_toko', 'konsumen_temp','invoice_detail.stok'])
                        ->whereMonth('created_at', $month)
                        ->whereYear('created_at', $year)
                        ->get();

        $invoices_void = $this->where('void', 1)
                        ->select('id', 'total', 'konsumen_id', 'kode', 'konsumen_temp_id', 'diskon', 'add_fee', 'updated_at')
                        ->with(['konsumen.kode_toko', 'konsumen_temp','invoice_detail.stok'])
                        ->whereMonth('updated_at', $month)
                        ->whereYear('updated_at', $year)
                        ->get();

        foreach ($invoices as $invoice) {
            $total_beli = 0;
            if ($invoice->invoice_detail) {
                foreach ($invoice->invoice_detail as $detail) {
                    if ($detail->stok) {
                        $total_beli += $detail->stok->harga_beli * $detail->jumlah;
                    }
                }
            }

            $invoice->total_beli = $total_beli;
            $invoice->profit = $invoice->total - $total_beli - $invoice->diskon + $invoice->add_fee;

        }

        foreach ($invoices_void as $invoice) {
            $total_beli = 0;
            if ($invoice->invoice_detail) {
                foreach ($invoice->invoice_detail as $detail) {
                    if ($detail->stok) {
                        $total_beli += $detail->stok->harga_beli * $detail->jumlah;
                    }
                }
            }

            $invoice->total_beli = $total_beli;
            $invoice->profit = ($invoice->total - $total_beli - $invoice->diskon + $invoice->add_fee) * -1; // Profit void adalah negatif dari profit sebenarnya

        }

        return [
            "invoice" => $invoices,
            'invoice_void' => $invoices_void,
        ];

    }
}
