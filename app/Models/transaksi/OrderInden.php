<?php

namespace App\Models\transaksi;

use App\Models\db\Barang\Barang;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderInden extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function invoice_jual_sales()
    {
        return $this->belongsTo(InvoiceJualSales::class, 'invoice_jual_sales_id');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }
}
