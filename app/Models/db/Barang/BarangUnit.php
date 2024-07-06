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
}
