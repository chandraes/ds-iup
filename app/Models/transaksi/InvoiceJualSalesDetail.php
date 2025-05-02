<?php

namespace App\Models\transaksi;

use App\Models\db\Barang\Barang;
use App\Models\db\Barang\BarangStokHarga;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceJualSalesDetail extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function invoiceJualSales()
    {
        return $this->belongsTo(InvoiceJualSales::class, 'invoice_jual_sales_id');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    public function barangStokHarga()
    {
        return $this->belongsTo(BarangStokHarga::class, 'barang_stok_harga_id');
    }
}
