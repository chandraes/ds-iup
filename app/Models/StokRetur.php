<?php

namespace App\Models;

use App\Models\db\Barang\Barang; // TAMBAHKAN INI
use App\Models\db\Barang\BarangStokHarga;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokRetur extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Relasi ke produk
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    public function sources()
    {
        return $this->hasMany(StokReturSource::class);
    }
}
