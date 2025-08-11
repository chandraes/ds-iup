<?php

namespace App\Models\transaksi;

use App\Models\db\Konsumen;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KeranjangJualKonsumen extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = ['sistem_pembayaran_word'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function konsumen()
    {
        return $this->belongsTo(Konsumen::class, 'konsumen_id');
    }

    public function getSistemPembayaranWordAttribute()
    {
        return match ($this->pembayaran) {
            1 => 'Cash',
            2 => 'Tempo',
            3 => 'Titipan',
            default => '-',
        };
    }

    public function keranjang_jual()
    {
        return $this->hasMany(KeranjangJual::class, 'keranjang_jual_konsumen_id');
    }

}
