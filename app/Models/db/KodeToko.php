<?php

namespace App\Models\db;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KodeToko extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function konsumen()
    {
        return $this->hasMany(Konsumen::class);
    }
}
