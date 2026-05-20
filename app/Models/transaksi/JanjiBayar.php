<?php

namespace App\Models\transaksi;

use App\Models\db\Konsumen;
use Illuminate\Database\Eloquent\Model;

class JanjiBayar extends Model
{
    protected $guarded = ['id'];

    protected $appends = ['status_label'];

    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            0 => '<span class="badge bg-secondary">Pending</span>',
            1 => '<span class="badge bg-success">Lunas</span',
            99 => '<span class="badge bg-danger">Batal</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }

    public function konsumen()
    {
        return $this->belongsTo(Konsumen::class, 'konsumen_id');
    }

    public function details()
    {
        return $this->hasMany(JanjiBayarDetail::class, 'janji_bayar_id');
    }
}
