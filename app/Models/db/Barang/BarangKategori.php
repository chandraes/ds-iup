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
        return $this->hasMany(Barang::class);
    }

    public function barang_nama()
    {
        return $this->hasMany(BarangNama::class);
    }
}
