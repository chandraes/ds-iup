<?php

namespace App\Models\db;

use App\Models\GantiRugi;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class);
    }

    public function ganti_rugi()
    {
        return $this->hasMany(GantiRugi::class);
    }
}
