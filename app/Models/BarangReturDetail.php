<?php

namespace App\Models;

use App\Models\db\Barang\BarangStokHarga;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangReturDetail extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function barang_retur()
    {
        return $this->belongsTo(BarangRetur::class);
    }

    public function stok()
    {
        return $this->belongsTo(BarangStokHarga::class, 'barang_stok_harga_id');
    }

    public function getNfQtyAttribute()
    {
        return number_format($this->qty, 0, ',', '.');
    }
}
