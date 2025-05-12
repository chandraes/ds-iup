<?php

namespace App\Models\transaksi;

use App\Models\db\Konsumen;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderInden extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function konsumen()
    {
        return $this->belongsTo(Konsumen::class);
    }

    public function detail()
    {
        return $this->hasMany(OrderIndenDetail::class);
    }
}
