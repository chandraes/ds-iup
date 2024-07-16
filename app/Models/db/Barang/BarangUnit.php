<?php

namespace App\Models\db\Barang;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $unitRowspan
 */
class BarangUnit extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public $unitRowspan = 0;

    public function types()
    {
        return $this->hasMany(BarangType::class);
    }

    public function scopeFilterByUnit($query, $unitFilter)
    {
        if ($unitFilter) {
            $query->where('id', $unitFilter);
        }
    }

    public function calculateUnitRowspan()
    {
        $this->unitRowspan = 0;
        $this->load('types'); // Ensure types are loaded
        foreach ($this->types as $type) {
            $type->calculateTypeRowspan($type->id); // Assuming this method exists and modifies $type
            $this->unitRowspan += $type->typeRowspan; // Ensure typeRowspan is being set in calculateTypeRowspan
        }
    }
}
