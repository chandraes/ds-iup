<?php

namespace App\Models\transaksi;

use App\Models\db\Konsumen;
use Illuminate\Database\Eloquent\Model;

class JanjiBayarKeranjang extends Model
{
    protected $guarded = ['id'];

    public function konsumen()
    {
        return $this->belongsTo(Konsumen::class, 'konsumen_id');
    }

    public function invoice_jual()
    {
        return $this->belongsTo(InvoiceJual::class, 'invoice_jual_id');
    }
}
