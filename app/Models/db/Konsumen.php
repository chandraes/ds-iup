<?php

namespace App\Models\db;

use App\Models\KasKonsumen;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Konsumen extends Model
{
    use HasFactory;

    const CASH = 1;
    const TEMPO = 2;

    protected $guarded = ['id'];

    protected $appends = ['sistem_pembayaran', 'nf_plafon', 'full_kode'];

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

    

}
