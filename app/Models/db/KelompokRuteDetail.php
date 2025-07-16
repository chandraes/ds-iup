<?php

namespace App\Models\db;

use App\Models\Wilayah;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KelompokRuteDetail extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function kelompokRute()
    {
        return $this->belongsTo(KelompokRute::class);
    }

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class);
    }
}
