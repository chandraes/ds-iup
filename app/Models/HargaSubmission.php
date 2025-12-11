<?php

namespace App\Models;

use App\Models\db\Barang\BarangStokHarga;
use Illuminate\Database\Eloquent\Model;

class HargaSubmission extends Model
{
    protected $guarded = ['id'];

    protected $appends = ['nf_harga_ajuan', 'nf_min_jual_ajuan'];

    public function stok()
    {
        return $this->belongsTo(BarangStokHarga::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getNfHargaAjuanAttribute()
    {
        return number_format($this->harga_ajuan, 0,',','.');
    }

    public function getNfMinJualAjuanAttribute()
    {
        return number_format($this->min_jual_ajuan, 0,',','.');
    }
}
