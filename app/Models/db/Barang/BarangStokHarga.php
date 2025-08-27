<?php

namespace App\Models\db\Barang;

use App\Models\db\Satuan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangStokHarga extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $appends = ['nf_harga', 'nf_stok', 'nf_harga_beli', 'tanggal', 'nf_min_jual', 'nf_stok_awal'];

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    public function getTanggalAttribute()
    {
        Carbon::setLocale('id');

        return Carbon::parse($this->created_at)->translatedFormat('d F Y');
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

    public function satuan()
    {
        return $this->belongsTo(Satuan::class, 'satuan_id');
    }

    public function getNfHargaAttribute()
    {
        return number_format($this->harga, 0, ',', '.');
    }

    public function getNfMinJualAttribute()
    {
        return $this->min_jual ? number_format($this->min_jual, 0, ',', '.') : '';
    }

    public function getNfStokAttribute()
    {
        return number_format($this->stok, 0, ',', '.');
    }

    public function getNfStokAwalAttribute()
    {
        return number_format($this->stok_awal, 0, ',', '.');
    }

    public function getNfHargaBeliAttribute()
    {
        return number_format($this->harga_beli, 0, ',', '.');
    }

    public function barangStok($jenis, $unitFilter = null, $typeFilter = null, $kategoriFilter = null, $barangNamaFilter = null)
    {
        $query = $this->with(['unit', 'type', 'kategori', 'barang_nama', 'barang.satuan', 'barang.detail_types'])
            ->whereHas('barang', function ($query) use ($jenis) {
                $query->where('jenis', $jenis);
            })
            ->where('stok', '>', 0)
            ->orderBy('barang_unit_id')
            ->orderBy('barang_type_id')
            ->orderBy('barang_kategori_id')
            ->orderBy('barang_nama_id')
            ->orderBy('barang_id');

        if (! is_null($unitFilter)) {
            $query->where('barang_unit_id', $unitFilter);
        }

        if (! is_null($typeFilter)) {
            $query->where('barang_type_id', $typeFilter);
        }

        if (! is_null($kategoriFilter)) {
            $query->where('barang_kategori_id', $kategoriFilter);
        }
        // dd($barangNamaFilter);
        if (! is_null($barangNamaFilter)) {
            $query->where('barang_nama_id', $barangNamaFilter);
        }

        $data = $query->get()
            ->groupBy([
                'barang_unit_id',
                'barang_type_id',
                'barang_kategori_id',
                'barang_nama_id',
                'barang_id',
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

        // dd($data);
        return $data;

    }

    public function barangStokV3($jenis, $unitFilter = null, $typeFilter = null, $kategoriFilter = null, $barangNamaFilter = null)
    {
        $query = $this->with(['unit', 'type', 'kategori', 'barang_nama', 'barang.satuan', 'barang.detail_types'])
            ->whereHas('barang', function ($query) use ($jenis) {
                $query->where('jenis', $jenis);
            })
            ->where('hide', 0)
            ->orderBy('barang_unit_id')
            ->orderBy('barang_type_id')
            ->orderBy('barang_kategori_id')
            ->orderBy('barang_nama_id')
            ->orderBy('barang_id')
            ->orderBy('created_at', 'asc');

        if (! is_null($unitFilter)) {
            $query->where('barang_unit_id', $unitFilter);
        }

        if (! is_null($typeFilter)) {
            $query->where('barang_type_id', $typeFilter);
        }

        if (! is_null($kategoriFilter)) {
            $query->where('barang_kategori_id', $kategoriFilter);
        }
        // dd($barangNamaFilter);
        if (! is_null($barangNamaFilter)) {
            $query->where('barang_nama_id', $barangNamaFilter);
        }

        $data = $query->get()
            ->groupBy([
                'barang_unit_id',
                'barang_type_id',
                'barang_kategori_id',
                'barang_nama_id',
                'barang_id',
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

        // dd($data);
        return $data;

    }

     public function barangStokAll($unitFilter = null, $typeFilter = null, $kategoriFilter = null, $barangNamaFilter = null)
    {
        $query = $this->with(['unit', 'type', 'kategori', 'barang_nama', 'barang.satuan', 'barang.detail_types'])
            ->where('hide', 0)
            ->orderBy('barang_unit_id')
            ->orderBy('barang_type_id')
            ->orderBy('barang_kategori_id')
            ->orderBy('barang_nama_id')
            ->orderBy('barang_id')
            ->orderBy('created_at', 'asc');

        if (!empty($unitFilter)) {
            $query->whereIn('barang_unit_id', $unitFilter);
        }

        if (! is_null($typeFilter)) {
            $query->where('barang_type_id', $typeFilter);
        }

        if (! is_null($kategoriFilter)) {
            $query->where('barang_kategori_id', $kategoriFilter);
        }
        // dd($barangNamaFilter);
        if (! is_null($barangNamaFilter)) {
            $query->where('barang_nama_id', $barangNamaFilter);
        }

        $data = $query->get()
            ->groupBy([
                'barang_unit_id',
                'barang_type_id',
                'barang_kategori_id',
                'barang_nama_id',
                'barang_id',
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

        // dd($data);
        return $data;

    }

    public function barangStokPdf($jenis, $unitFilter = null, $typeFilter = null, $kategoriFilter = null, $barangNamaFilter = null)
    {
        $query = $this->with(['unit', 'type', 'kategori', 'barang_nama', 'barang.satuan', 'barang.detail_types'])
            ->whereHas('barang', function ($query) use ($jenis) {
                $query->where('jenis', $jenis);
            })->where('stok', '>', 0)
            ->orderBy('barang_unit_id')
            ->orderBy('barang_type_id')
            ->orderBy('barang_kategori_id')
            ->orderBy('barang_nama_id')
            ->orderBy('barang_id');

        if (! is_null($unitFilter)) {
            $query->where('barang_unit_id', $unitFilter);
        }

        if (! is_null($typeFilter)) {
            $query->where('barang_type_id', $typeFilter);
        }

        if (! is_null($kategoriFilter)) {
            $query->where('barang_kategori_id', $kategoriFilter);
        }
        // dd($barangNamaFilter);
        if (! is_null($barangNamaFilter)) {
            $query->where('barang_nama_id', $barangNamaFilter);
        }

        $data = $query->get();

        return $data;
    }

    public function barangStokV2($jenis, $unitFilter = null, $typeFilter = null, $kategoriFilter = null, $barangNamaFilter = null)
    {

        $data = [];
        $barang_unit = BarangUnit::with(['types'])
                        // when unitFilter is not null
            ->when($unitFilter, function ($query, $unitFilter) {
                return $query->where('id', $unitFilter);
            })
            ->get();
        $no_unit = 0;

        foreach ($barang_unit as $u) {
            $data[$no_unit]['unit_id'] = $u->id;
            $data[$no_unit]['unit'] = $u->nama;
            $data[$no_unit]['unitRowspan'] = 0;

            $no_type = 0;
            foreach ($u->types as $t) {
                $data[$no_unit]['types'][$no_type]['type_id'] = $t->id;
                $data[$no_unit]['types'][$no_type]['nama_tipe'] = $t->nama;
                $data[$no_unit]['types'][$no_type]['typeRowspan'] = 0;

                $barang = Barang::with(['kategori', 'barang_nama', 'satuan', 'stok_harga' => function ($query) {
                    $query->where('stok', '>', 0)
                        ->orWhere(function ($query) {
                            $query->where('stok', '=', 0)
                                ->orderBy('id', 'desc')
                                ->limit(1);
                        });
                }])
                    ->where('barang_unit_id', $u->id)
                    ->where('barang_type_id', $t->id)
                    ->where('jenis', $jenis)
                    ->when($typeFilter, function ($query, $typeFilter) {
                        return $query->where('barang_type_id', $typeFilter);
                    })
                    ->when($kategoriFilter, function ($query, $kategoriFilter) {
                        return $query->where('barang_kategori_id', $kategoriFilter);
                    })
                    ->when($barangNamaFilter, function ($query, $barangNamaFilter) {
                        return $query->where('barang_nama_id', $barangNamaFilter);
                    })

                    ->select('barang_kategori_id', 'barang_nama_id')
                    ->groupBy('barang_kategori_id', 'barang_nama_id')
                    ->orderBy('barang_kategori_id')
                    ->orderBy('barang_nama_id')
                    ->get();

                $no_nama = 0;
                $no_kategori = 0;
                $barangNamaRowspan = 0;

                foreach ($barang as $b) {
                    if (isset($data[$no_unit]['types'][$no_type]['kategori'][$no_kategori]['kategori_id']) &&
                        $data[$no_unit]['types'][$no_type]['kategori'][$no_kategori]['kategori_id'] != $b->kategori->id) {
                        $no_kategori++;
                        $no_nama = 0;
                        $barangNamaRowspan = 0;
                    }
                    // dd($b);
                    $data[$no_unit]['types'][$no_type]['kategori'][$no_kategori]['kategori_id'] = $b->kategori->id;
                    $data[$no_unit]['types'][$no_type]['kategori'][$no_kategori]['nama_kategori'] = $b->kategori->nama;
                    $data[$no_unit]['types'][$no_type]['kategori'][$no_kategori]['kategoriRowspan'] = $barangNamaRowspan;
                    $data[$no_unit]['types'][$no_type]['kategori'][$no_kategori]['barang_nama'][$no_nama]['barang_nama_id'] = $b->barang_nama_id;
                    $data[$no_unit]['types'][$no_type]['kategori'][$no_kategori]['barang_nama'][$no_nama]['nama'] = $b->barang_nama->nama;
                    $data[$no_unit]['types'][$no_type]['kategori'][$no_kategori]['barang_nama'][$no_nama]['nama'] = $b->barang_nama->nama;
                    $data[$no_unit]['types'][$no_type]['kategori'][$no_kategori]['barang_nama'][$no_nama]['barangNamaRowspan'] = 0;

                    $barangBanyak = Barang::with(['satuan', 'stok_harga' => function ($query) {
                        $query->where('stok', '>', 0)
                            ->orWhere(function ($query) {
                                $query->where('stok', '=', 0)
                                    ->orderBy('id', 'desc')
                                    ->limit(1);
                            });
                    }])
                        ->where('barang_unit_id', $u->id)
                        ->where('jenis', $jenis)
                        ->where('barang_type_id', $t->id)
                        ->where('barang_kategori_id', $b->kategori->id)
                        ->where('barang_nama_id', $b->barang_nama_id)
                        ->get();

                    $no_barang = 0;
                    $barangRowspan = 0;

                    foreach ($barangBanyak as $bb) {
                        $data[$no_unit]['types'][$no_type]['kategori'][$no_kategori]['barang_nama'][$no_nama]['barang'][$no_barang]['id'] = $bb->id;
                        $data[$no_unit]['types'][$no_type]['kategori'][$no_kategori]['barang_nama'][$no_nama]['barang'][$no_barang]['kode'] = $bb->kode;
                        $data[$no_unit]['types'][$no_type]['kategori'][$no_kategori]['barang_nama'][$no_nama]['barang'][$no_barang]['merk'] = $bb->merk;
                        $data[$no_unit]['types'][$no_type]['kategori'][$no_kategori]['barang_nama'][$no_nama]['barang'][$no_barang]['satuan'] = $bb->satuan->nama;
                        $data[$no_unit]['types'][$no_type]['kategori'][$no_kategori]['barang_nama'][$no_nama]['barang'][$no_barang]['barangRowspan'] = 0;

                        $stokHargaRowspan = 0;
                        $no_stok = 0;

                        if ($bb->stok_harga->count() == 0) {
                            $data[$no_unit]['types'][$no_type]['kategori'][$no_kategori]['barang_nama'][$no_nama]['barang'][$no_barang]['stokHarga'][$no_stok]['stok_id'] = '-';
                            $data[$no_unit]['types'][$no_type]['kategori'][$no_kategori]['barang_nama'][$no_nama]['barang'][$no_barang]['stokHarga'][$no_stok]['harga'] = '-';
                            $data[$no_unit]['types'][$no_type]['kategori'][$no_kategori]['barang_nama'][$no_nama]['barang'][$no_barang]['stokHarga'][$no_stok]['stok'] = '-';
                            $data[$no_unit]['types'][$no_type]['kategori'][$no_kategori]['barang_nama'][$no_nama]['barang'][$no_barang]['stokHarga'][$no_stok]['harga_beli'] = '-';
                            $data[$no_unit]['types'][$no_type]['kategori'][$no_kategori]['barang_nama'][$no_nama]['barang'][$no_barang]['stokHarga'][$no_stok]['satuan'] = '-';

                            $stokHargaRowspan++;
                            $no_stok++;
                            $data[$no_unit]['unitRowspan'] += 1;
                            $data[$no_unit]['types'][$no_type]['typeRowspan'] += 1;
                            $data[$no_unit]['types'][$no_type]['kategori'][$no_kategori]['kategoriRowspan'] += 1;
                            $data[$no_unit]['types'][$no_type]['kategori'][$no_kategori]['barang_nama'][$no_nama]['barangNamaRowspan'] += 1;
                            $data[$no_unit]['types'][$no_type]['kategori'][$no_kategori]['barang_nama'][$no_nama]['barang'][$no_barang]['barangRowspan'] += 1;
                            $barangNamaRowspan++;
                        }
                        foreach ($bb->stok_harga as $stokHarga) {
                            $data[$no_unit]['types'][$no_type]['kategori'][$no_kategori]['barang_nama'][$no_nama]['barang'][$no_barang]['stokHarga'][$no_stok]['stok_id'] = $stokHarga->id;
                            $data[$no_unit]['types'][$no_type]['kategori'][$no_kategori]['barang_nama'][$no_nama]['barang'][$no_barang]['stokHarga'][$no_stok]['harga'] = $stokHarga->harga;
                            $data[$no_unit]['types'][$no_type]['kategori'][$no_kategori]['barang_nama'][$no_nama]['barang'][$no_barang]['stokHarga'][$no_stok]['stok'] = $stokHarga->stok;
                            $data[$no_unit]['types'][$no_type]['kategori'][$no_kategori]['barang_nama'][$no_nama]['barang'][$no_barang]['stokHarga'][$no_stok]['harga_beli'] = $stokHarga->harga_beli;
                            $data[$no_unit]['types'][$no_type]['kategori'][$no_kategori]['barang_nama'][$no_nama]['barang'][$no_barang]['stokHarga'][$no_stok]['satuan'] = $bb->satuan->nama;

                            $stokHargaRowspan++;
                            $no_stok++;
                            $data[$no_unit]['unitRowspan'] += 1;
                            $data[$no_unit]['types'][$no_type]['typeRowspan'] += 1;
                            $data[$no_unit]['types'][$no_type]['kategori'][$no_kategori]['kategoriRowspan'] += 1;
                            $data[$no_unit]['types'][$no_type]['kategori'][$no_kategori]['barang_nama'][$no_nama]['barangNamaRowspan'] += 1;
                            $data[$no_unit]['types'][$no_type]['kategori'][$no_kategori]['barang_nama'][$no_nama]['barang'][$no_barang]['barangRowspan'] += 1;
                            $barangNamaRowspan++;
                        }

                        $no_barang++;
                    }

                    $no_nama++;
                }

                $no_kategori++;
                $no_type++;
            }
            $no_unit++;
        }

        // dd($data);
        return $data;

    }
}
