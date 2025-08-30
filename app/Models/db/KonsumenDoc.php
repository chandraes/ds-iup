<?php

namespace App\Models\db;

use App\Models\db\Barang\BarangUnit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KonsumenDoc extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = ['file_url'];

    public function konsumen()
    {
        return $this->belongsTo(Konsumen::class);
    }

    public function barang_unit()
    {
        return $this->belongsTo(BarangUnit::class);
    }

    public function getFileUrlAttribute()
    {
        return asset('storage/' . $this->file_path);
    }
}
