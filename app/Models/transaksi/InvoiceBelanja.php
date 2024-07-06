<?php

namespace App\Models\transaksi;

use App\Models\db\Barang\BarangHistory;
use App\Models\db\Supplier;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceBelanja extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $appends = [
        'nf_diskon',
        'nf_ppn',
        'nf_add_fee',
        'nf_total',
        'nf_dp',
        'nf_dp_ppn',
        'nf_sisa',
        'nf_sisa_ppn',
        'id_jatuh_tempo',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function detail()
    {
        return $this->hasMany(InvoiceBelanjaDetail::class, 'invoice_belanja_id');
    }

    public function items()
    {
        return $this->hasManyThrough(
            BarangHistory::class,
            InvoiceBelanjaDetail::class,
            'invoice_belanja_id',
            'id',
            'id',
            'barang_history_id'
        );
    }

    public function getNfDiskonAttribute()
    {
        return number_format($this->diskon, 0, ',', '.');
    }

    public function getNfPpnAttribute()
    {
        return number_format($this->ppn, 0, ',', '.');
    }

    public function getNfAddFeeAttribute()
    {
        return number_format($this->add_fee, 0, ',', '.');
    }

    public function getNfTotalAttribute()
    {
        return number_format($this->total, 0, ',', '.');
    }

    public function getNfDpAttribute()
    {
        return number_format($this->dp, 0, ',', '.');
    }

    public function getNfDpPpnAttribute()
    {
        return number_format($this->dp_ppn, 0, ',', '.');
    }

    public function getNfSisaAttribute()
    {
        return number_format($this->sisa, 0, ',', '.');
    }

    public function getNfSisaPpnAttribute()
    {
        return number_format($this->sisa_ppn, 0, ',', '.');
    }

    public function getIdJatuhTempoAttribute()
    {
        // use Carbon\Carbon;
        return $this->jatuh_tempo ? Carbon::parse($this->jatuh_tempo)->format('d F Y') : '';
    }
}
