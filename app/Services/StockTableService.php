<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class StockTableService
{
    /** @param array{unit?: array<int, int|string>, type?: int|string|null, kategori?: int|string|null, barang_nama?: int|string|null} $filters */
    public function query(array $filters, float $ppnRate): Builder
    {
        $query = DB::table('barang_stok_hargas as s')
            ->leftJoin('barangs as b', 'b.id', '=', 's.barang_id')
            ->leftJoin('barang_units as u', 'u.id', '=', 's.barang_unit_id')
            ->leftJoin('barang_types as t', 't.id', '=', 's.barang_type_id')
            ->leftJoin('barang_kategoris as k', 'k.id', '=', 's.barang_kategori_id')
            ->leftJoin('barang_namas as n', 'n.id', '=', 's.barang_nama_id')
            ->leftJoin('satuans as a', 'a.id', '=', 'b.satuan_id')
            ->leftJoin('harga_submissions as h', function (JoinClause $join): void {
                $join->on('h.barang_stok_harga_id', '=', 's.id')->where('h.status', 1);
            })
            ->where('s.hide', 0)->where(function (Builder $query): void {
                $query->where('s.stok', '>', 0)->orWhere('b.is_active', 1);
            })
            ->select(['s.id', 'u.nama as perusahaan', 't.nama as bidang', 'k.nama as kelompok', 'n.nama as nama',
                'b.kode', 'b.merk', 's.stok_awal', 's.stok', 'a.nama as satuan', 'b.jenis'])
            ->selectRaw('ROUND(CASE WHEN b.jenis = 1 THEN COALESCE(h.harga_ajuan, s.harga) * (1 + ? / 100.0) ELSE COALESCE(h.harga_ajuan, s.harga) END, 0) as harga_jual_ppn', [$ppnRate]);

        if (! empty($filters['unit'])) {
            $query->whereIn('s.barang_unit_id', $filters['unit']);
        }
        foreach (['type' => 'barang_type_id', 'kategori' => 'barang_kategori_id', 'barang_nama' => 'barang_nama_id'] as $filter => $column) {
            if (isset($filters[$filter])) {
                $query->where('s.'.$column, $filters[$filter]);
            }
        }

        return $query;
    }

    public function filterOptions(array $filters): array
    {
        $units = DB::table('barang_units')->select('id', 'nama')->orderBy('id')->get();
        $selectType = DB::table('barang_types')->select('id', 'nama')->when(! empty($filters['unit']), fn ($q) => $q->whereIn('barang_unit_id', $filters['unit']))->orderBy('id')->get();
        $categoryIds = DB::table('barangs as options_b')->join('barang_types as options_t', 'options_t.id', '=', 'options_b.barang_type_id')
            ->when(! empty($filters['unit']), fn ($q) => $q->whereIn('options_t.barang_unit_id', $filters['unit']));
        $selectKategori = DB::table('barang_kategoris')->select('id', 'nama')->when(! empty($filters['unit']), fn ($q) => $q->whereIn('id', (clone $categoryIds)->select('options_b.barang_kategori_id')))->orderBy('id')->get();
        $selectBarangNama = DB::table('barang_namas')->select('id', 'nama')->when(! empty($filters['unit']), fn ($q) => $q->whereIn('id', (clone $categoryIds)->select('options_b.barang_nama_id')))->orderBy('id')->get();

        return compact('units', 'selectType', 'selectKategori', 'selectBarangNama');
    }

    public function adminQuery(array $filters, float $ppnRate): Builder
    {
        $query = $this->query($filters, $ppnRate)
            ->addSelect(['s.barang_id', 's.harga', 's.harga_beli', 's.min_jual', 'h.id as submission_id', 'h.harga_ajuan', 'h.min_jual_ajuan'])
            ->selectRaw('CASE WHEN b.jenis = 1 THEN s.harga_beli * (1 + ? / 100.0) ELSE s.harga_beli END as harga_beli_ppn', [$ppnRate])
            ->selectRaw('CASE WHEN b.jenis = 1 THEN s.harga_beli * (1 + ? / 100.0) ELSE s.harga_beli END * s.stok as total_beli', [$ppnRate])
            ->selectRaw('CASE WHEN b.jenis = 1 THEN COALESCE(h.harga_ajuan,s.harga) * (1 + ? / 100.0) ELSE COALESCE(h.harga_ajuan,s.harga) END * s.stok as total_jual', [$ppnRate])
            ->selectRaw('CASE WHEN s.harga_beli = 0 THEN NULL ELSE (COALESCE(h.harga_ajuan,s.harga) - s.harga_beli) / (s.harga_beli * 1.0) * 100 END as margin');
        if (! empty($filters['filter_harga'])) {
            $query->whereIn('s.barang_id', DB::table('barang_stok_hargas')->select('barang_id')->where('harga', 0));
        }

        return $query;
    }

    public function totals(Builder $query): array
    {
        $totals = DB::query()->fromSub($query, 'stock_totals')
            ->selectRaw('COALESCE(SUM(total_beli),0) as beli, COALESCE(SUM(total_jual),0) as jual')->first();

        return ['beli' => (float) $totals->beli, 'jual' => (float) $totals->jual, 'profit' => (float) $totals->jual - (float) $totals->beli];
    }

    public function search(Builder $query, string $search): void
    {
        if ($search === '') {
            return;
        }
        $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search).'%';
        $query->where(function (Builder $query) use ($pattern): void {
            foreach (['u.nama', 't.nama', 'k.nama', 'n.nama', 'b.kode', 'b.merk', 'a.nama'] as $column) {
                $query->orWhereRaw($column." LIKE ? ESCAPE '!'", [$pattern]);
            }
        });
    }

    public function order(Builder $query): void
    {
        foreach (['s.barang_unit_id', 's.barang_type_id', 's.barang_kategori_id', 's.barang_nama_id', 's.barang_id', 's.created_at', 's.id'] as $column) {
            $query->orderBy($column);
        }
    }
}
