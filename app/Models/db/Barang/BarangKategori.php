<?php

namespace App\Models\db\Barang;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangKategori extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function types()
    {
        return $this->hasMany(BarangType::class);
    }

    public function barangs()
    {
        return $this->hasMany(Barang::class, 'barang_kategori_id');
    }

    public function barang_nama()
    {
        return $this->hasMany(BarangNama::class);
    }

    // scopeRowspan untuk menghitung jumlah rowspan ke barang_nama
    public function scopeRowspan($query)
    {
        return $query->withCount('barang_nama');
    }

    // scopeFilterByKategori untuk filter data berdasarkan kategori
    public function scopeFilterByKategori($query, $kategoriFilter)
    {
        if ($kategoriFilter) {
            $query->where('id', $kategoriFilter);
        }
    }
}
