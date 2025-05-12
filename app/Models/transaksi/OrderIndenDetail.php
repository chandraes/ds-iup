<?php

namespace App\Models\transaksi;

use App\Models\db\Barang\Barang;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderIndenDetail extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function orderInden()
    {
        return $this->belongsTo(OrderInden::class);
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }
}
