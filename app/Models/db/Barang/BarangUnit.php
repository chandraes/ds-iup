<?php

namespace App\Models\db\Barang;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangUnit extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function types()
    {
        return $this->hasMany(BarangType::class);
    }
}
