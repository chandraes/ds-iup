<?php

namespace App\Models\transaksi;

use App\Models\db\Barang\Barang;
use App\Models\db\Barang\BarangStokHarga;
use App\Models\db\Satuan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceJualSalesDetail extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $appends = [
        'nf_jumlah',
        'nf_harga_satuan',
        'total',
        'nf_total',
    ];

    public function invoiceJualSales()
    {
        return $this->belongsTo(InvoiceJualSales::class, 'invoice_jual_sales_id');
    }

    public function satuan_grosir()
    {
        return $this->belongsTo(Satuan::class, 'satuan_grosir_id');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    public function barangStokHarga()
    {
        return $this->belongsTo(BarangStokHarga::class, 'barang_stok_harga_id');
    }

    public function getNfJumlahAttribute()
    {
        return number_format($this->jumlah, 0, ',', '.');
    }

    public function getNfHargaSatuanAttribute()
    {
        return number_format($this->harga_satuan, 0, ',', '.');
    }

    public function getNfDiskonAttribute()
    {
        return number_format($this->diskon, 0, ',', '.');
    }

    public function getTotalAttribute()
    {
        return $this->jumlah * ($this->harga_satuan-$this->diskon+$this->ppn);
    }

    public function getNfTotalAttribute()
    {
        return number_format($this->total, 0, ',', '.');
    }
}
