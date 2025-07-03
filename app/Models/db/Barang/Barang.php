<?php

namespace App\Models\db\Barang;

use App\Models\db\Satuan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $appends = ['text_jenis'];

    public function satuan()
    {
        return $this->belongsTo(Satuan::class, 'satuan_id');
    }

    public function detail_types()
    {
        return $this->hasMany(BarangDetailType::class, 'barang_id');
    }

    public function unit()
    {
        return $this->belongsTo(BarangUnit::class, 'barang_unit_id')->withDefault([
            'nama' => '-',
        ]);
    }

    public function type()
    {
        return $this->belongsTo(BarangType::class, 'barang_type_id')->withDefault([
            'nama' => '-',
        ]);
    }

    public function kategori()
    {
        return $this->belongsTo(BarangKategori::class, 'barang_kategori_id')->withDefault([
            'nama' => '-',
        ]);
    }

    public function barang_nama()
    {
        return $this->belongsTo(BarangNama::class, 'barang_nama_id')->withDefault([
            'nama' => '-',
        ]);
    }

    public function stok_harga()
    {
        return $this->hasMany(BarangStokHarga::class, 'barang_id');
    }

    public function histories()
    {
        return $this->hasMany(BarangHistory::class, 'barang_id');
    }

    public function subpg()
    {
        return $this->belongsTo(Subpg::class, 'subpg_id')->withDefault([
            'nama' => '-',
        ]);
    }

    public function scopeFilterByKategori($query, $kategori)
    {
        if (! empty($kategori)) {
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

    public function getBarang($filters)
    {
        $query = $this->with(['unit', 'type', 'kategori', 'barang_nama', 'satuan']);

        if (isset($filters['unit'])) {
            $query->where('barang_unit_id', $filters['unit']);
        }

        if (isset($filters['type'])) {
            $query->where('barang_type_id', $filters['type']);
        }

        if (isset($filters['kategori'])) {
            $query->where('barang_kategori_id', $filters['kategori']);
        }

        if (isset($filters['nama'])) {
            $query->where('barang_nama_id', $filters['nama']);
        }

        $query->orderBy('barang_unit_id')
            ->orderBy('barang_type_id')
            ->orderBy('barang_kategori_id')
            ->orderBy('barang_nama_id');

        if (isset($filters['pagination']) && $filters['pagination'] != '') {
            return $query->paginate($filters['pagination']);
        } else {
            return $query->paginate(10);
        }

    }
}
