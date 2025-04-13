<?php

namespace App\Models\transaksi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceJualCicil extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = ['tanggal', 'nf_nominal', 'nf_ppn', 'total', 'nf_total'];

    public function invoice_jual()
    {
        return $this->belongsTo(InvoiceJual::class);
    }

    public function getTanggalAttribute()
    {
        return $this->created_at->format('d-m-Y');
    }

    public function getNfNominalAttribute()
    {
        return number_format($this->nominal, 0, ',', '.');
    }

    public function getNfPpnAttribute()
    {
        return number_format($this->ppn, 0, ',', '.');
    }

    public function getTotalAttribute()
    {
        return $this->nominal + $this->ppn;
    }

    public function getNfTotalAttribute()
    {
        return number_format($this->total, 0, ',', '.');
    }
}
