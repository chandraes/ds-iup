<?php

namespace App\Models\transaksi;

use App\Models\Config;
use App\Models\db\Barang\BarangStokHarga;
use App\Models\db\Konsumen;
use App\Models\GroupWa;
use App\Models\KasBesar;
use App\Models\KasKonsumen;
use App\Models\KonsumenTemp;
use App\Models\Pajak\RekapPpn;
use App\Models\PpnKeluaran;
use App\Models\PpnMasukan;
use App\Models\Rekening;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InvoiceJual extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $appends = ['tanggal', 'id_jatuh_tempo', 'dpp', 'nf_ppn', 'nf_grand_total', 'nf_dp', 'nf_dp_ppn', 'nf_sisa_ppn', 'nf_sisa_tagihan',  'dpp_setelah_diskon'];

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

    public function getFullKodeAttribute()
    {
        return  str_pad($this->nomor, 3, '0', STR_PAD_LEFT) . '/' . Carbon::parse($this->created_at)->format('Y');
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

    public function invoice_detail()
    {
        return $this->hasMany(InvoiceJualDetail::class);
    }

    public function bayar($id)
    {
        $kas = new KasBesar();
        $inv = $this->find($id);

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
                'uraian' => 'Pelunasan ' . $inv->kode,
                'jenis' => '1',
                'nominal' => $sisa_tagihan,
                'saldo' => $kas->saldoTerakhir($kas_ppn) + $sisa_tagihan,
                'nama_rek' => $rekening->nama_rek,
                'no_rek' => $rekening->no_rek,
                'bank' => $rekening->bank,
                'modal_investor_terakhir' => $kas->modalInvestorTerakhir($kas_ppn),
            ]);

            // update kas konsumen
            $kas_konsumen = new KasKonsumen();
            $sisaKasKonsumen = $kas_konsumen->sisaTerakhir($inv->konsumen_id);
            $sisaAkhirKonsumen = $sisaKasKonsumen - $sisa_tagihan;

            $kas_konsumen->create([
                'konsumen_id' => $inv->konsumen_id,
                'invoice_jual_id' => $inv->id,
                'uraian' => 'Pelunasan ' . $inv->kode,
                'bayar' => $sisa_tagihan,
                'sisa' => $sisaAkhirKonsumen < 0 ? 0 : $sisaAkhirKonsumen,
            ]);

            $inv->update([
                'lunas' => 1,
            ]);

            DB::commit();

            $getKas = $kas->getKas();

            $dbPPn = new PpnMasukan();
            $dbRekapPpn = new RekapPpn();
            $saldoTerakhirPpn = $dbRekapPpn->saldoTerakhir();
            $ppnMasukan = $dbPPn->where('is_finish', 0)->sum('nominal') + $saldoTerakhirPpn;
            $dbPpnKeluaran = new PpnKeluaran();
            $ppnKeluaran = $dbPpnKeluaran->where('is_expired', 0)->where('is_finish', 0)->sum('nominal');

            if ($kas_ppn == 1) {
                $addPesan = "Sisa Saldo Kas Besar: \n".
                            "Rp. ".number_format($getKas['saldo_ppn'], 0, ',', '.')."\n\n".
                            "Total Modal Investor PPN: \n".
                            "Rp. ".number_format($kas->modalInvestorTerakhir(1), 0, ',', '.')."\n\n";
             } else {
                 $addPesan = "Sisa Saldo Kas Besar: \n".
                             "Rp. ".number_format($getKas['saldo_non_ppn'], 0, ',', '.')."\n\n".
                             "Total Modal Investor Non PPN: \n".
                             "Rp. ".number_format($getKas['modal_investor_non_ppn'], 0, ',', '.')."\n\n";
             }
            //  dd($kasMana);
            $pesan = "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n".
                        "*PELUNASAN JUAL BARANG*\n".
                        "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n\n".
                        "Uraian :  *".$store->uraian."*\n\n".
                        "Nilai    :  *Rp. ".number_format($store->nominal, 0, ',', '.')."*\n\n".
                        "Ditransfer ke rek:\n\n".
                        "Bank      : ".$store->bank."\n".
                        "Nama    : ".$store->nama_rek."\n".
                        "No. Rek : ".$store->no_rek."\n\n".
                        "==========================\n".
                        $addPesan.
                        "Total PPn Masukan : \n".
                        "Rp. ".number_format($ppnMasukan, 0, ',', '.')."\n\n".
                        "Total PPn Keluaran : \n".
                        "Rp. ".number_format($ppnKeluaran, 0, ',', '.')."\n\n".
                        "Terima kasih 🙏🙏🙏\n";

            $group = GroupWa::where('untuk', $kasMana)->first()->nama_group;
            //  dd($group);
            $kas->sendWa($group, $pesan);

            return [
                'status' => 'success',
                'message' => 'Berhasil melunasi invoice!!'
            ];

        } catch (\Throwable $th) {
            DB::rollBack();
            return ['status' => 'error', 'message' => $th->getMessage()];
        }
    }

    public function store_ppn($inv_id, $ppn)
    {
        $db = new PpnKeluaran();
        $inv = $this->find($inv_id);
        $saldo = $db->saldoTerakhir() + $ppn;

        $db->create([
            'invoice_jual_id' => $inv_id,
            'nominal' => $ppn,
            'saldo' => $saldo,
            'uraian' => 'Pelunasan '. $inv->kode,
            'dipungut' => $inv->ppn_dipungut,
        ]);


        return true;

    }

    public function store_ppn_cicil($inv_id, $kode, $nominal, $dipungut)
    {
        $db = new PpnKeluaran();
        $saldo = $db->saldoTerakhir() + $nominal;

        $db->create([
            'invoice_jual_id' => $inv_id,
            'nominal' => $nominal,
            'saldo' => $saldo,
            'uraian' => 'Cicilan '. $kode,
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
                'message' => 'Invoice sudah di-void!!'
            ];
        }

        $dp = $data->ppn_dipungut == 1 ? $data->dp + $data->dp_ppn : $data->dp;
        $stateWa = 0;

        try {
            DB::beginTransaction();

            if ($dp > 0) {
                $dbKas = new KasBesar();
                $saldoKasBesar = $dbKas->saldoTerakhir($data->kas_ppn);

                if ($saldoKasBesar < $dp) {
                    return [
                        'status' => 'error',
                        'message' => 'Saldo kas besar tidak mencukupi!!. Saldo saat ini: Rp. '.number_format($saldoKasBesar, 0, ',', '.')
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
            if($data->konsumen_id)
            {
                $kas_konsumen = new KasKonsumen();
                $sisaKasKonsumen = $kas_konsumen->sisaTerakhir($data->konsumen_id);
                $sisaAkhirKonsumen = $sisaKasKonsumen - $data->grand_total;

                if ($sisaAkhirKonsumen < 0) {
                    $sisaAkhirKonsumen = 0;
                }

                $kas_konsumen->create([
                    'konsumen_id' => $data->konsumen_id,
                    'invoice_jual_id' => $data->id,
                    'uraian' => 'Void ' . $data->kode,
                    'bayar' => $data->grand_total,
                    'sisa' => $sisaAkhirKonsumen,
                ]);
            }


            $data->update([
                'void' => 1,
            ]);

            DB::commit();

            if ($stateWa == 1) {
                $gw = new GroupWa();
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
                'message' => 'Berhasil void invoice!!'
            ];


        } catch (\Throwable $th) {

            DB::rollBack();

            return [
                'status' => 'error',
                'message' => 'Terjadi Kesalahan! '.$th->getMessage()
            ];
        }


    }

    private function pengembalianDp($id)
    {
        $data = $this->find($id);

        $totalDp = $data->ppn_dipungut == 1 ? $data->dp + $data->dp_ppn : $data->dp;

        $kas = new KasBesar();
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
                'message' => 'Invoice ini tidak memiliki konsumen!!'
            ];
        }

        $kas = new KasBesar();

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
                'uraian' => 'Cicilan ' . $inv->kode,
                'jenis' => '1',
                'nominal' => $totalCicil,
                'saldo' => $kas->saldoTerakhir($kas_ppn) + $totalCicil,
                'nama_rek' => $rekening->nama_rek,
                'no_rek' => $rekening->no_rek,
                'bank' => $rekening->bank,
                'modal_investor_terakhir' => $kas->modalInvestorTerakhir($kas_ppn),
            ]);

            // update kas konsumen
            $kas_konsumen = new KasKonsumen();
            $sisaKasKonsumen = $kas_konsumen->sisaTerakhir($inv->konsumen_id);
            $sisaAkhirKonsumen = $sisaKasKonsumen - $totalCicil;

            $kas_konsumen->create([
                'konsumen_id' => $inv->konsumen_id,
                'invoice_jual_id' => $inv->id,
                'uraian' => 'Cicilan ' . $inv->kode,
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

            $dbPPn = new PpnMasukan();
            $dbRekapPpn = new RekapPpn();
            $saldoTerakhirPpn = $dbRekapPpn->saldoTerakhir();
            $ppnMasukan = $dbPPn->where('is_finish', 0)->sum('nominal') + $saldoTerakhirPpn;
            $dbPpnKeluaran = new PpnKeluaran();
            $ppnKeluaran = $dbPpnKeluaran->where('is_expired', 0)->where('is_finish', 0)->sum('nominal');

            $addMessageUp = "Sisa Cicilan konsumen : \n".
                            "Rp. ".number_format($inv->sisa_tagihan, 0, ',', '.')."\n\n";

            $addMessage =   "Total PPn Masukan : \n".
                            "Rp. ".number_format($ppnMasukan, 0, ',', '.')."\n\n".
                            "Total PPn Keluaran : \n".
                            "Rp. ".number_format($ppnKeluaran, 0, ',', '.')."\n\n";

            $dbWa = new GroupWa();

            $pesan = $dbWa->generateMessage(1, 'CICILAN JUAL BARANG', $kas_ppn, $store->uraian, $store->nominal, $rekening, $addMessage, $addMessageUp);

            $group = $dbWa->where('untuk', $kasMana)->first()->nama_group;
            //  dd($group);
            $dbWa->sendWa($group, $pesan);

            return [
                'status' => 'success',
                'message' => 'Berhasil melakukan cicilan invoice!!'
            ];

        } catch (\Throwable $th) {
            DB::rollBack();
            return ['status' => 'error', 'message' => $th->getMessage()];
        }
    }
}
