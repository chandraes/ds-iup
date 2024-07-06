<?php

namespace App\Models\db\Barang;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangStokHarga extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $appends = ['nf_harga', 'nf_stok'];

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    public function getNfHargaAttribute()
    {
        return number_format($this->harga, 0, ',', '.');
    }

    public function getNfStokAttribute()
    {
        return number_format($this->stok, 0, ',', '.');
    }
}
