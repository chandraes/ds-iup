<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokReturCart extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function stok_retur()
    {
        return $this->belongsTo(StokRetur::class);
    }
}
