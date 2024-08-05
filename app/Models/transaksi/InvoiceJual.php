<?php

namespace App\Models\transaksi;

use App\Models\Config;
use App\Models\db\Konsumen;
use App\Models\GroupWa;
use App\Models\KasBesar;
use App\Models\KonsumenTemp;
use App\Models\PpnKeluaran;
use App\Models\Rekening;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InvoiceJual extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function getTanggalAttribute()
    {
        return Carbon::parse($this->created_at)->format('d-m-Y');
    }

    public function getIdJatuhTempoAttribute()
    {
        return Carbon::parse($this->jatuh_tempo)->format('d-m-Y');
    }

    public function getDppAttribute()
    {
        return number_format($this->total, 0, ',', '.');
    }

    public function getNfPpnAttribute()
    {
        return number_format($this->ppn, 0, ',', '.');
    }

    public function getNfPphAttribute()
    {
        return number_format($this->pph, 0, ',', '.');
    }

    public function getNfGrandTotalAttribute()
    {
        return number_format($this->grand_total, 0, ',', '.');
    }

    public function getNfDpAttribute()
    {
        return number_format($this->dp, 0, ',', '.');
    }

    public function getNfDpPpnAttribute()
    {
        return number_format($this->dp_ppn, 0, ',', '.');
    }

    public function getSisaPpnAttribute()
    {
        return number_format($this->ppn - $this->dp_ppn, 0, ',', '.');
    }

    public function getSisaTagihanAttribute()
    {
        return number_format($this->grand_total - $this->dp - $this->dp_ppn, 0, ',', '.');
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
                $this->store_ppn($id, $inv->ppn-$inv->dp_ppn);
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

            $inv->update([
                'lunas' => 1,
            ]);

            DB::commit();

            $getKas = $kas->getKas();

            $dbPpnKeluaran = new PpnKeluaran();
            $ppnKeluaran = $dbPpnKeluaran->saldoTerakhir();

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
        ]);


        return true;

    }
}
