<?php

namespace App\Models\transaksi;

use App\Models\db\Konsumen;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceJualSales extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $appends = ['tanggal', 'dpp', 'nf_ppn',
    'nf_grand_total', 'nf_dp', 'nf_dp_ppn', 'nf_sisa_ppn',
    'nf_sisa_tagihan',  'dpp_setelah_diskon', 'sistem_pembayaran_word', 'tanggal_en',
];

    public function getTanggalAttribute()
    {
        return Carbon::parse($this->created_at)->format('d-m-Y');
    }

    public function getTanggalEnAttribute()
    {
        return Carbon::parse($this->created_at)->format('Y-m-d');
    }

    public function konsumen()
    {
        return $this->belongsTo(Konsumen::class, 'konsumen_id');
    }

    public function invoice_detail()
    {
        return $this->hasMany(InvoiceJualSalesDetail::class, 'invoice_jual_sales_id');
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

}
