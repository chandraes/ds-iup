<?php

namespace App\Models\db\Barang;

use App\Models\db\Pajak;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangStokHarga extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $appends = ['nf_harga', 'nf_stok', 'nf_harga_beli'];

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

    public function getNfHargaBeliAttribute()
    {
        return number_format($this->harga_beli, 0, ',', '.');
    }
}
