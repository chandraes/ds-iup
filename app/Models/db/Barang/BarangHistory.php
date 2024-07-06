<?php

namespace App\Models\db\Barang;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangHistory extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $appends = ['nf_jumlah', 'nf_harga'];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function getNfJumlahAttribute()
    {
        return number_format($this->jumlah, 0, ',', '.');
    }

    public function getNfHargaAttribute()
    {
        return number_format($this->harga, 0, ',', '.');
    }
}
