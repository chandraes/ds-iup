<?php

namespace App\Models\transaksi;

use App\Models\db\Konsumen;
use Illuminate\Database\Eloquent\Model;

class JanjiBayar extends Model
{
    protected $guarded = ['id'];

    public function konsumen()
    {
        return $this->belongsTo(Konsumen::class, 'konsumen_id');
    }
}
