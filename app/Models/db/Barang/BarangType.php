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
        return $this->belongsTo(BarangUnit::class, 'barang_unit_id');
    }

    public function barangs()
    {
        return $this->hasMany(Barang::class);
    }

    public function scopeFilterByType($query, $type)
    {
        if (! empty($type)) {
            $query->where('id', $type); // Replace 'type_column' with the actual column name you want to filter by
        }
    }

    public static function calculateTypeRowspan($typeId)
    {
        return self::where('id', $typeId)->count();
    }
}
