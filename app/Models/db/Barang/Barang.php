<?php

namespace App\Models\db\Barang;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $appends = ['text_jenis'];

    public function detail_types()
    {
        return $this->hasMany(BarangDetailType::class, 'barang_id');
    }

    public function type()
    {
        return $this->belongsTo(BarangType::class, 'barang_type_id');
    }

    public function kategori()
    {
        return $this->belongsTo(BarangKategori::class, 'barang_kategori_id');
    }

    public function barang_nama()
    {
        return $this->belongsTo(BarangNama::class, 'barang_nama_id');
    }

    public function stok_harga()
    {
        return $this->hasMany(BarangStokHarga::class, 'barang_id');
    }

    public function histories()
    {
        return $this->hasMany(BarangHistory::class, 'barang_id');
    }

    public function scopeFilterByKategori($query, $kategori)
    {
        if (!empty($kategori)) {
            $query->where('barang_kategori_id', $kategori); // Adjust 'kategori_id' to the actual column name you want to filter by
        }
    }

    public function getTextJenisAttribute()
    {
        $jenis = $this->jenis;
        if ($jenis == 1) {
            return 'PPN';
        } elseif ($jenis == 2) {
            return 'Non-PPN';
        } elseif ($jenis == 3) {
            return 'PPN & Non-PPN';
        } else {
            return '-';
        }
    }
}
