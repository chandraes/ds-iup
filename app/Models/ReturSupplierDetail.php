<?php

namespace App\Models;

use App\Models\db\Barang\Barang;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturSupplierDetail extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function getNfQtyAttribute()
    {
        return number_format($this->qty, 0, ',','.');
    }
}
