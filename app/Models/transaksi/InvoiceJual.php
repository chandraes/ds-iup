<?php

namespace App\Models\transaksi;

use App\Models\db\Konsumen;
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

    public function generateNomor()
    {
        return $this->max('nomor') + 1;
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
                'saldo' => $kas->saldoTerakhir($inv->kas_ppn) + $sisa_tagihan,
                'nama_rek' => $rekening->nama_rek,
                'no_rek' => $rekening->no_rek,
                'bank' => $rekening->bank,
                'modal_investor_terakhir' => $kas->modalInvestorTerakhir($inv->kas_ppn),
            ]);

            // DB::commit();
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
