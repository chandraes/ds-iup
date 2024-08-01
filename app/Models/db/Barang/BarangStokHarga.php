<?php

namespace App\Models\db\Barang;

use App\Models\db\Pajak;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangStokHarga extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $appends = ['nf_harga', 'nf_stok', 'nf_harga_beli'];

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    public function unit()
    {
        return $this->belongsTo(BarangUnit::class, 'barang_unit_id');
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

    public function getNfHargaAttribute()
    {
        return number_format($this->harga, 0, ',', '.');
    }

    public function getNfStokAttribute()
    {
        return number_format($this->stok, 0, ',', '.');
    }

    public function getNfHargaBeliAttribute()
    {
        return number_format($this->harga_beli, 0, ',', '.');
    }

    public function barangStok($jenis, $unitFilter = null, $typeFilter = null, $kategoriFilter = null, $barangNamaFilter = null)
    {
        $query = $this->with(['unit', 'type', 'kategori', 'barang_nama', 'barang.satuan'])
            ->whereHas('barang', function ($query) use ($jenis) {
                $query->where('jenis', $jenis);
            })
            ->orderBy('barang_unit_id');

        if (!is_null($unitFilter)) {
            $query->where('barang_unit_id', $unitFilter);
        }

        if (!is_null($typeFilter)) {
            $query->where('barang_type_id', $typeFilter);
        }

        if (!is_null($kategoriFilter)) {
            $query->where('barang_kategori_id', $kategoriFilter);
        }
        // dd($barangNamaFilter);
        if (!is_null($barangNamaFilter)) {
            $query->where('barang_nama_id', $barangNamaFilter);
        }

        $data = $query->get()
            ->groupBy([
                'barang_unit_id',
                'barang_type_id',
                'barang_kategori_id',
                'barang_nama_id',
                'barang_id'
            ]);

        foreach ($data as $unitId => $types) {
            $unitRowspan = $types->sum(function ($type) {
                return $type->sum(function ($kategori) {
                    return $kategori->sum(function ($barangNama) {
                        return $barangNama->sum(function ($barang) {
                            return $barang->count();
                        });
                    });
                });
            });

            foreach ($types as $typeId => $categories) {
                $typeRowspan = $categories->sum(function ($kategori) {
                    return $kategori->sum(function ($barangNama) {
                        return $barangNama->sum(function ($barang) {
                            return $barang->count();
                        });
                    });
                });

                foreach ($categories as $kategoriId => $barangs) {
                    $kategoriRowspan = $barangs->sum(function ($barangNama) {
                        return $barangNama->sum(function ($barang) {
                            return $barang->count();
                        });
                    });

                    foreach ($barangs as $namaId => $items) {
                        $namaRowspan = $items->sum(function ($barang) {
                            return $barang->count();
                        });

                        foreach ($items as $barangId => $stokHargas) {
                            $barangRowspan = $stokHargas->count();

                            foreach ($stokHargas as $stokHarga) {
                                $stokHarga->unitRowspan = $unitRowspan;
                                $stokHarga->typeRowspan = $typeRowspan;
                                $stokHarga->kategoriRowspan = $kategoriRowspan;
                                $stokHarga->namaRowspan = $namaRowspan;
                                $stokHarga->barangRowspan = $barangRowspan;
                            }
                        }
                    }
                }
            }
        }

            return $data;

    }
}
