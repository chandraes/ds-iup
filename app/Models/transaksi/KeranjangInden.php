<?php

namespace App\Models\transaksi;

use App\Models\db\Barang\Barang;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KeranjangInden extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $appends = ['nf_jumlah'];

    public function getNfJumlahAttribute()
    {
        return number_format($this->jumlah, 0, ',','.');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }
}
