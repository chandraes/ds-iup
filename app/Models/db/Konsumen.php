<?php

namespace App\Models\db;

use App\Models\KasKonsumen;
use App\Models\Wilayah;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Konsumen extends Model
{
    use HasFactory;

    const CASH = 1;
    const TEMPO = 2;

    protected $guarded = ['id'];

    protected $appends = ['sistem_pembayaran', 'nf_plafon', 'full_kode'];

    public function provinsi()
    {
        return $this->belongsTo(Wilayah::class, 'provinsi_id', 'id');
    }

    public function kabupaten_kota()
    {
        return $this->belongsTo(Wilayah::class, 'kabupaten_kota_id', 'id');
    }

    public function kecamatan()
    {
        return $this->belongsTo(Wilayah::class, 'kecamatan_id', 'id');
    }

    public function sales_area()
    {
        return $this->belongsTo(SalesArea::class);
    }

    public function generateKode()
    {
        $kode = $this->max('kode');
        return $kode + 1;
    }

    public function getFullKodeAttribute()
    {
        return 'K' . str_pad($this->kode, 2, '0', STR_PAD_LEFT);
    }

    public function getSistemPembayaranAttribute()
    {
        return $this->pembayaran == self::CASH ? 'Cash' : 'Tempo';
    }

    public function getNfPlafonAttribute()
    {
        return number_format($this->plafon, 0, ',', '.');
    }

    public function kas()
    {
        return $this->hasMany(KasKonsumen::class, 'konsumen_id');
    }

    public function scopeFilter($query, $filters)
    {
        if (isset($filters['area']) && $filters['area']) {
            $query->where('sales_area_id', $filters['area']);
        }

        if (isset($filters['kecamatan']) && $filters['kecamatan']) {
            $query->where('kecamatan_id', $filters['kecamatan']);
        }

        return $query;
    }

}
