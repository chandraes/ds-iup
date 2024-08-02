<?php

namespace App\Models\transaksi;

use App\Models\db\Konsumen;
use App\Models\KonsumenTemp;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceJual extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

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
}
