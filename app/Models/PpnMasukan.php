<?php

namespace App\Models;

use App\Models\transaksi\InvoiceBelanja;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PpnMasukan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $appends = ['tanggal', 'nf_nominal', 'nf_saldo'];

    public function invoiceBelanja()
    {
        return $this->belongsTo(InvoiceBelanja::class);
    }

    public function getTanggalAttribute()
    {
        return date('d-m-Y', strtotime($this->created_at));
    }

    public function getNfNominalAttribute()
    {
        return number_format($this->nominal, 0, ',', '.');
    }

    public function getNfSaldoAttribute()
    {
        return number_format($this->saldo, 0, ',', '.');
    }

    public function saldoTerakhir()
    {
        return $this->orderBy('id', 'desc')->first()->saldo ?? 0;
    }
}
