<?php

namespace App\Models\db;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class KonsumenPlafonHistory extends Model
{
    protected $guarded = ['id'];

    public function konsumen()
    {
        return $this->belongsTo(Konsumen::class);
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
