<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CustomerInvoiceTableService
{
    /** @param array<string, mixed> $filters */
    public function query(array $filters = []): Builder
    {
        $installments = DB::table('invoice_jual_cicils')->select('invoice_jual_id')
            ->selectRaw('SUM(nominal) AS nominal, SUM(ppn) AS ppn, COUNT(*) AS payment_count')->groupBy('invoice_jual_id');
        $query = DB::table('invoice_juals as i')
            ->leftJoin('konsumens as c', 'c.id', '=', 'i.konsumen_id')
            ->leftJoin('kode_tokos as kt', 'kt.id', '=', 'c.kode_toko_id')
            ->leftJoin('wilayahs as kab', 'kab.id', '=', 'c.kabupaten_kota_id')
            ->leftJoin('wilayahs as kec', 'kec.id', '=', 'c.kecamatan_id')
            ->leftJoin('karyawans as s', 's.id', '=', 'i.karyawan_id')
            ->leftJoinSub($installments, 'payments', fn ($join) => $join->on('payments.invoice_jual_id', '=', 'i.id'))
            ->where('i.void', 0)->where('i.titipan', 0)->where('i.lunas', 0)
            ->select(['i.id', 'i.created_at', 'i.karyawan_id', 'i.konsumen_id', 'i.nomor', 'i.kode',
                'i.total', 'i.diskon', 'i.ppn', 'i.add_fee', 'i.grand_total', 'i.dp', 'i.dp_ppn',
                'i.sisa_ppn', 'i.sisa_tagihan', 'i.jatuh_tempo', 'i.ppn_dipungut', 'i.kas_ppn',
                'c.kode as konsumen_kode', 'c.nama as konsumen_nama', 'c.plafon', 'kt.kode as kode_toko',
                'kab.nama_wilayah as kabupaten', 'kec.nama_wilayah as kecamatan'])
            ->selectRaw("COALESCE(s.nama, '-') AS sales, COALESCE(payments.nominal, 0) + COALESCE(payments.ppn, 0) AS cicilan, COALESCE(payments.payment_count, 0) AS cicilan_count");

        if (! empty($filters['expired'])) {
            $query->where('i.jatuh_tempo', $filters['expired'] === 'no' ? '>' : '<=', now());
        }
        if (! empty($filters['apa_ppn'])) {
            $query->where('i.kas_ppn', $filters['apa_ppn'] === 'yes' ? 1 : 0);
        }
        foreach (['karyawan_id' => 'i.karyawan_id', 'konsumen_id' => 'i.konsumen_id',
            'kecamatan_id' => 'c.kecamatan_id', 'kabupaten_id' => 'c.kabupaten_kota_id'] as $filter => $column) {
            if (isset($filters[$filter]) && $filters[$filter] !== '') {
                $query->where($column, $filters[$filter]);
            }
        }

        return $query;
    }

    public function search(Builder $query, string $search): void
    {
        if (trim($search) === '') {
            return;
        }
        preg_match_all('/"([^"]+)"|(\S+)/u', $search, $matches, PREG_SET_ORDER);
        $sqlite = $query->getConnection()->getDriverName() === 'sqlite';
        $castType = $sqlite ? 'TEXT' : 'CHAR';
        $moneyColumns = ['i.total', 'i.diskon', 'i.ppn', 'i.add_fee', 'i.grand_total', 'i.dp', 'i.dp_ppn',
            'COALESCE(payments.nominal, 0) + COALESCE(payments.ppn, 0)', 'i.sisa_ppn', 'i.sisa_tagihan', 'c.plafon'];
        foreach ($matches as $match) {
            $token = $match[1] !== '' ? $match[1] : $match[2];
            $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $token).'%';
            $query->where(function (Builder $query) use ($pattern, $token, $castType, $moneyColumns, $sqlite): void {
                foreach (['i.kode', 's.nama', 'c.nama', 'kt.kode', 'kab.nama_wilayah', 'kec.nama_wilayah', 'i.jatuh_tempo', 'i.created_at'] as $column) {
                    $query->orWhereRaw($column." LIKE ? ESCAPE '!'", [$pattern]);
                }
                $codeExpression = $sqlite
                    ? "'K' || CASE WHEN LENGTH(CAST(c.kode AS TEXT)) < 2 THEN '0' ELSE '' END || CAST(c.kode AS TEXT)"
                    : "CONCAT('K', CASE WHEN LENGTH(CAST(c.kode AS CHAR)) < 2 THEN '0' ELSE '' END, c.kode)";
                $query->orWhereRaw('('.$codeExpression.") LIKE ? ESCAPE '!'", [$pattern]);
                $customerCode = preg_match('/\AK([0-9]+)\z/i', $token, $codeMatches) ? $codeMatches[1] : $token;
                if (ctype_digit($customerCode)) {
                    $query->orWhere('c.kode', (int) $customerCode)->orWhere('i.nomor', (int) $customerCode);
                }
                if (preg_match('/\A-?[0-9]+(?:\.[0-9]{3})*\z/', $token)) {
                    $number = str_replace('.', '', $token);
                    foreach ($moneyColumns as $column) {
                        $formatted = $sqlite
                            ? "REPLACE(PRINTF('%,d', COALESCE((".$column."), 0)), ',', '.')"
                            : 'FORMAT(COALESCE(('.$column."), 0), 0, 'de_DE')";
                        $query->orWhereRaw($formatted." LIKE ? ESCAPE '!'", [$pattern])
                            ->orWhereRaw('CAST(('.$column.') AS '.$castType.') = ?', [$number]);
                    }
                }
            });
        }
    }

    /** @param array<int, array{column?: int|string, dir?: string}> $orders */
    public function order(Builder $query, array $orders): void
    {
        $columns = [0 => DB::raw('DATE(i.created_at)'), 1 => 'sales', 2 => 'kabupaten', 3 => 'c.kode', 4 => 'c.nama',
            5 => 'c.plafon', 6 => 'i.nomor', 7 => 'i.total', 8 => 'i.grand_total', 9 => 'i.dp',
            10 => 'i.dp_ppn', 11 => 'cicilan', 12 => 'i.sisa_ppn', 13 => 'i.sisa_tagihan', 14 => 'i.jatuh_tempo'];
        $ordered = false;
        foreach ($orders as $order) {
            $column = $columns[(int) ($order['column'] ?? -1)] ?? null;
            if ($column !== null) {
                $query->orderBy($column, ($order['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc');
                $ordered = true;
            }
        }
        if (! $ordered) {
            $query->orderByRaw('DATE(i.created_at) ASC');
        }
        $query->orderBy('i.id');
    }

    /** @return array<string, int> */
    public function totals(Builder $query): array
    {
        $columns = ['total', 'diskon', 'ppn', 'add_fee', 'grand_total', 'dp', 'dp_ppn', 'cicilan', 'sisa_ppn', 'sisa_tagihan'];
        $totals = DB::query()->fromSub($query->reorder(), 'filtered_invoices')
            ->selectRaw(implode(', ', array_map(fn (string $column): string => 'COALESCE(SUM('.$column.'), 0) AS '.$column, $columns)))->first();

        return array_map(fn ($value): int => (int) $value, (array) $totals);
    }
}
