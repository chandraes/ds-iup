<?php

namespace App\Models\transaksi;

use Illuminate\Database\Eloquent\Model;

class JanjiBayarDetail extends Model
{
    protected $guarded = ['id'];

    public function invoice_jual()
    {
        return $this->belongsTo(InvoiceJual::class, 'invoice_jual_id');
    }
}
