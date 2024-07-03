<?php

namespace App\Models\db;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventarisJenis extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'sum_jumlah', 'nf_sum_jumlah', 'sum_total', 'nf_sum_total'];

    public function kategori()
    {
        return $this->belongsTo(InventarisKategori::class, 'kategori_id');
    }

    public function rekap()
    {
        return $this->hasMany(InventarisRekap::class, 'inventaris_jenis_id');
    }

    public function getSumJumlahAttribute()
    {
        return $this->rekap->sum('jumlah');
    }

    public function getNfSumJumlahAttribute()
    {
        return number_format($this->sum_jumlah, 0, ',','.');
    }

    public function getSumTotalAttribute()
    {
        return $this->rekap->sum('total');
    }

    public function getNfSumTotalAttribute()
    {
        return number_format($this->sum_total, 0, ',','.');
    }

}
