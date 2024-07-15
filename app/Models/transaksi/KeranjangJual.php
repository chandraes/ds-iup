<?php

namespace App\Models\transaksi;

use App\Models\db\Barang\Barang;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KeranjangJual extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $appends = ['nf_harga', 'nf_jumlah', 'nf_total'];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function getNfHargaAttribute()
    {
        return number_format($this->harga_satuan, 0, ',', '.');
    }

    public function getNfJumlahAttribute()
    {
        return number_format($this->jumlah, 0, ',', '.');
    }

    public function getNfTotalAttribute()
    {
        return number_format($this->total, 0, ',', '.');
    }
}
