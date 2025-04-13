<?php

namespace App\Models\db\Barang;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $unitRowspan
 */
class BarangUnit extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $attributes = [
        // 'unitRowspan' => 0, // default value
        // add other default attributes here
    ];

    // Optional: You can also use an accessor if needed
    public function getUnitRowspanAttribute()
    {
        return $this->attributes['unitRowspan'] ?? 0;
    } // Variable to store rowspan for the unit

    protected $appends = ['unitRowspan'];

    public function types()
    {
        return $this->hasMany(BarangType::class);
    }

    public function scopeFilterByUnit($query, $unitFilter)
    {
        if ($unitFilter) {
            $query->where('id', $unitFilter);
        }
    }

    public function calculateUnitRowspan()
    {
        $this->unitRowspan = 0;
        $this->load('types'); // Ensure types are loaded
        foreach ($this->types as $type) {
            $type->calculateTypeRowspan($type->id); // Assuming this method exists and modifies $type
            $this->unitRowspan += $type->typeRowspan; // Ensure typeRowspan is being set in calculateTypeRowspan
        }
    }

    public function barangAll($unitFilter = null, $typeFilter = null, $kategoriFilter = null, $jenisFilter = null, $barangNamaFilter = null)
    {
        // Initial query optimized with necessary eager loading
        $query = $this->with(['types.barangs.kategori', 'types.barangs.barang_nama', 'types.barangs.satuan',
            'types.barangs.detail_types.type',

            'types.barangs' => function ($query) use ($typeFilter, $kategoriFilter, $jenisFilter, $barangNamaFilter) {
                if ($typeFilter) {
                    $query->where('barang_type_id', $typeFilter);
                }
                if ($kategoriFilter) {
                    $query->where('barang_kategori_id', $kategoriFilter);
                }
                if ($jenisFilter) {
                    $query->where('jenis', $jenisFilter);
                }
                // inside types.barangs.barang_nama relation i want to filter it by nama
                if ($barangNamaFilter) {
                    $query->whereHas('barang_nama', function ($query) use ($barangNamaFilter) {
                        $query->where('nama', $barangNamaFilter);
                    });
                }
            }]);

        if ($unitFilter) {
            $query->where('id', $unitFilter);
        }

        $units = $query->get();

        // Simplify the calculation of rowspan values using collection methods
        $units->each(function ($unit) {
            $unit->unitRowspan = 0; // Initialize unitRowspan
            $unit->types->each(function ($type) use ($unit) {
                if (isset($type->barangs)) { // Ensure barangs exists
                    $type->groupedBarangs = $type->barangs->groupBy('kategori.nama');
                    $type->typeRowspan = 0; // Initialize typeRowspan

                    $type->groupedBarangs->each(function ($barangs, $kategoriNama) use ($type, $unit) {
                        $groupedByNama = $barangs->groupBy('barang_nama.nama');

                        $groupedByNama->each(function ($namaBarangs) use ($type, $unit, $barangs) { // Pass $barangs explicitly
                            $namaBarangs->each(function ($barang) use ($namaBarangs, $barangs) {
                                $barang->kategoriRowspan = $barangs->count();
                                $barang->namaRowspan = $namaBarangs->count();
                            });

                            $type->typeRowspan += $namaBarangs->count();
                            $unit->unitRowspan += $namaBarangs->count();
                        });
                    });
                }
            });
        });

        return $units;
    }

    // public function barangStok($jenis, $unitFilter = null, $typeFilter = null, $kategoriFilter = null, $barangNamaFilter = null)
    // {
    //     $unitsQuery = $this->with([
    //         'types' => function ($query) use ($typeFilter, $kategoriFilter, $jenis, $barangNamaFilter) {
    //             if ($typeFilter) {
    //                 $query->where('id', $typeFilter);
    //             }

    //             $query->with(['barangs' => function ($query) use ($kategoriFilter, $jenis, $barangNamaFilter) {
    //                 if ($kategoriFilter) {
    //                     $query->where('barang_kategori_id', $kategoriFilter);
    //                 }
    //                 if ($barangNamaFilter) {
    //                     $query->whereHas('barang_nama', function($query) use ($barangNamaFilter) {
    //                         $query->where('nama', 'like', '%' . $barangNamaFilter . '%');
    //                     });
    //                 }
    //                 $query->with(['kategori', 'barang_nama', 'stok_harga' => function($q) {
    //                     $q->where('stok', '>', 0);
    //                 }])->where('jenis', $jenis); // Eager load kategori and nama for each barang
    //             }])
    //             ->withCount('barangs as totalBarangs'); // Count barangs directly in the query
    //         },
    //     ]);

    //     if ($unitFilter) {
    //         $unitsQuery->where('id', $unitFilter);
    //     }

    //     $units = $unitsQuery->get();

    //     $units->loadMissing('types.barangs.kategori', 'types.barangs.barang_nama', 'types.barangs.stok_harga');

    //     foreach ($units as $unit) {
    //         $unit->unitRowspan = 0;

    //         foreach ($unit->types as $type) {
    //             $groupedBarangs = $type->barangs->groupBy('kategori.nama');
    //             $type->groupedBarangs = $groupedBarangs;
    //             $type->typeRowspan = 0;

    //             foreach ($groupedBarangs as $kategoriNama => $barangs) {
    //                 $groupedByNama = $barangs->groupBy('barang_nama.nama');

    //                 foreach ($groupedByNama as $nama => $namaBarangs) {
    //                     foreach ($namaBarangs as $barang) {
    //                         $barang->kategoriRowspan = 0;
    //                         $barang->namaRowspan = 0;
    //                         $barang->stokPpnRowspan = $barang->stok_harga->count();

    //                         $barang->kategoriRowspan += $barang->stokPpnRowspan;
    //                         $barang->namaRowspan += $barang->stokPpnRowspan;
    //                     }

    //                     $type->typeRowspan += $barang->namaRowspan;
    //                     $unit->unitRowspan += $barang->namaRowspan;
    //                 }
    //             }
    //         }
    //     }

    //     return $units;
    // }

    public function barangStokV2($jenis, $unitFilter = null, $typeFilter = null, $kategoriFilter = null, $barangNamaFilter = null)
    {

        $query = $this->with(['types.barangs', 'types.barangs.stok_harga'])
            ->whereHas('types.barangs.stok_harga', function ($query) {
                $query->where('stok', '>', 0);
            });

        if ($unitFilter) {
            $query->where('id', $unitFilter);
        }

        $units = $query->get();

        $units->each(function ($unit) {
            $unit->unitRowspan = 0;

            $unit->types->each(function ($type) use ($unit) {
                $type->barangs->load('stok_harga');

                $type->typeRowspan = 0;

                $type->barangs->groupBy('kategori_id')->each(function ($barangs, $kategoriId) use ($type, $unit) {
                    $kategoriRowspan = 0;

                    $barangs->groupBy('barang_nama_id')->each(function ($namaBarangs) use (&$kategoriRowspan, $type, $unit) {
                        $namaRowspan = 0;

                        $namaBarangs->each(function ($barang) use (&$namaRowspan) {
                            $stokCount = $barang->stok_harga->count();
                            $namaRowspan += $stokCount;

                            $barang->namaRowspan = $stokCount;
                            $barang->stokPpnRowspan = $stokCount;
                        });

                        $kategoriRowspan += $namaRowspan;
                        $type->typeRowspan += $namaRowspan;
                        $unit->unitRowspan += $namaRowspan;
                    });

                    if ($barangs->isNotEmpty()) {
                        $barangs->first()->kategoriRowspan = $kategoriRowspan;
                    }
                });
            });
        });

        return $units;
    }
}
