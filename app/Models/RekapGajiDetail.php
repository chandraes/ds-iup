<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekapGajiDetail extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function rekap_gaji()
    {
        return $this->belongsTo(RekapGaji::class);
    }
}
