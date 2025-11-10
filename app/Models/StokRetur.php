<?php

namespace App\Models;

use App\Models\db\Barang\BarangStokHarga;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokRetur extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function barang_stok_harga()
    {
        return $this->belongsTo(BarangStokHarga::class);
    }

    public function sources()
    {
        return $this->hasMany(StokReturSource::class);
    }
}
