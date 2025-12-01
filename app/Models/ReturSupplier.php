<?php

namespace App\Models;

use App\Models\db\Barang\BarangUnit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturSupplier extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function details()
    {
        return $this->hasMany(ReturSupplierDetail::class);
    }

    public function barang_unit()
    {
        return $this->belongsTo(BarangUnit::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
