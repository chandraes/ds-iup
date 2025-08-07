<?php

namespace App\Models\db\Barang;

use App\Models\db\Satuan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangGrosir extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id')->withDefault([
            'nama' => '-',
        ]);
    }

    public function satuan()
    {
        return $this->belongsTo(Satuan::class, 'satuan_id')->withDefault([
            'nama' => '-',
        ]);
    }
}
