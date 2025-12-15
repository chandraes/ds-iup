<?php

namespace App\Models\transaksi;

use App\Models\db\Barang\Barang;
use Illuminate\Database\Eloquent\Model;

class KeranjangBeliDetail extends Model
{
    protected $guarded = ['id'];
    protected $appends = ['nf_qty', 'nf_harga', 'nf_total'];

    public function keranjang()
    {
        return $this->belongsTo(KeranjangBeli::class);
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function getNfQtyAttribute()
    {
        return number_format($this->qty, 0, ',','.');
    }

    public function getNfHargaAttribute()
    {
        return number_format($this->harga, 0, ',','.');
    }

    public function getNfTotalAttribute()
    {
        return number_format($this->total, 0, ',','.');
    }
}
