<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UangGantung extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'ppn_kas' => 'boolean',
        'lunas' => 'boolean',
    ];

    protected $appends = ['nf_nominal'];

    public function getTanggalEnAttribute()
    {
        return $this->tanggal->format('Y-m-d');
    }

    public function getNfNominalAttribute()
    {
        return number_format($this->nominal, 0, ',', '.');
    }

    public function scopeLunas($query)
    {
        return $query->where('lunas', 1);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

