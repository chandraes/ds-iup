<?php

namespace App\Models\transaksi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceJualCicil extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function invoice_jual()
    {
        return $this->belongsTo(InvoiceJual::class);
    }
}
