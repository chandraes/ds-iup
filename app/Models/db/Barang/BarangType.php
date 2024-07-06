<?php

namespace App\Models\db\Barang;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $typeRowspan
 * @property \Illuminate\Support\Collection $groupedBarangs
 */
class BarangType extends Model
{
    use HasFactory;

    public $categoryRowspan = [];
    public $typeRowspan = 0;
    public $groupedBarangs;

    protected $guarded = ['id'];

    public function unit()
    {
        return $this->belongsTo(BarangUnit::class);
    }

    public function barangs()
    {
        return $this->hasMany(Barang::class);
    }
}
