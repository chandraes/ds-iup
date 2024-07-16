<?php

namespace App\Models\db\Barang;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function type()
    {
        return $this->belongsTo(BarangType::class, 'barang_type_id');
    }

    public function kategori()
    {
        return $this->belongsTo(BarangKategori::class, 'barang_kategori_id');
    }

    public function stok_ppn()
    {
        return $this->hasOne(BarangStokHarga::class, 'barang_id')->where('tipe', 'ppn');
    }

    public function stok_non_ppn()
    {
        return $this->hasOne(BarangStokHarga::class, 'barang_id')->where('tipe', 'non-ppn');
    }

    public function histories()
    {
        return $this->hasMany(BarangHistory::class, 'barang_id');
    }

    public function scopeFilterByKategori($query, $kategori)
    {
        if (!empty($kategori)) {
            $query->where('barang_kategori_id', $kategori); // Adjust 'kategori_id' to the actual column name you want to filter by
        }
    }
}
