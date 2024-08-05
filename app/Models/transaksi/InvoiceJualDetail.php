<?php

namespace App\Models\transaksi;

use App\Models\db\Barang\BarangStokHarga;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceJualDetail extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function getNfJumlahAttribute()
    {
        return number_format($this->jumlah, 0, ',', '.');
    }

    public function getNfHargaSatuanAttribute()
    {
        return number_format($this->harga_satuan, 0, ',', '.');
    }

    public function getNfTotalAttribute()
    {
        return number_format($this->total, 0, ',', '.');
    }

    public function invoice()
    {
        return $this->belongsTo(InvoiceJual::class);
    }

    public function stok()
    {
        return $this->belongsTo(BarangStokHarga::class, 'barang_stok_harga_id');
    }
}
