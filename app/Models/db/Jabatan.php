<?php

namespace App\Models\db;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function isSales(): bool
    {
        return mb_strtolower(trim($this->nama)) === 'sales';
    }

    public function isProtectedSales(): bool
    {
        return (int) $this->getKey() === 3 || $this->isSales();
    }

    public function karyawan()
    {
        return $this->hasMany(Karyawan::class);
    }
}
