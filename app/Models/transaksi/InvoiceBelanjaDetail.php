<?php

namespace App\Models\transaksi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceBelanjaDetail extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function invoice()
    {
        return $this->belongsTo(InvoiceBelanja::class, 'invoice_belanja_id');
    }
}
