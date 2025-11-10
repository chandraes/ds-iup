<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokReturSource extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function barang_retur_detail()
    {
        return $this->belongsTo(BarangReturDetail::class);
    }
}
