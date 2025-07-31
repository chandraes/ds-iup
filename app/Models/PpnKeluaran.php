<?php

namespace App\Models;

use App\Models\transaksi\InvoiceJual;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PpnKeluaran extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $appends = ['tanggal', 'tanggal_en', 'nf_nominal', 'nf_saldo'];

    public function saldoTerakhir()
    {
        return $this->orderBy('id', 'desc')->first()->saldo ?? 0;
    }

    public function invoiceJual()
    {
        return $this->belongsTo(InvoiceJual::class, 'invoice_jual_id');
    }

    public function getTanggalAttribute()
    {
        return date('d-m-Y', strtotime($this->created_at));
    }

    public function getTanggalEnAttribute()
    {
        return date('Y-m-d', strtotime($this->created_at));
    }

    public function getNfNominalAttribute()
    {
        return number_format($this->nominal, 0, ',', '.');
    }

    public function getNfSaldoAttribute()
    {
        return number_format($this->saldo, 0, ',', '.');
    }
}
