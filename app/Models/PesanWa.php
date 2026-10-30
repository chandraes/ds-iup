<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PesanWa extends Model
{
    use HasFactory;

    protected $fillable = [
        'pesan',
        'tujuan',
        'status',
        'last_attempt_at',
    ];

    protected function casts(): array
    {
        return ['last_attempt_at' => 'datetime'];
    }

    public function short_pesan()
    {
        return substr($this->pesan, 0, 1000).'.............';
    }
}
