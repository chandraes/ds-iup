<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class FormLainReportController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $ajax = $request->ajax();
        $filters = $request->validate(['ppn_kas' => 'nullable|in:0,1', 'bulan' => 'nullable|integer|between:1,12',
            'tahun' => 'nullable|integer|between:1901,2155', 'search' => $ajax ? 'nullable|array' : 'nullable|string|max:100',
            'search.value' => 'nullable|string|max:100']);
        if (! isset($filters['ppn_kas'])) {
            return view('rekap.lain-lain-select');
        }
        $cashType = (int) $filters['ppn_kas'];
        $month = (int) ($filters['bulan'] ?? now()->month);
        $year = (int) ($filters['tahun'] ?? now()->year);
        $search = trim($ajax ? ($filters['search']['value'] ?? '') : ($filters['search'] ?? ''));
        $period = Carbon::create($year, $month, 1)->startOfDay();
        $base = DB::table('kas_besars')->where('lain_lain', 1)->where('ppn_kas', $cashType);
        $query = (clone $base)->where('created_at', '>=', $period)->where('created_at', '<', $period->copy()->addMonth());
        if ($ajax) {
            $request->merge(['length' => min(100, max(1, $request->integer('length', 25))), 'start' => max(0, $request->integer('start')),
                'columns' => [], 'order' => [], 'search' => ['value' => $search, 'regex' => false]]);

            return DataTables::of($query->select(['id', 'created_at', 'uraian', 'jenis', 'nominal', 'nama_rek', 'no_rek', 'bank'])->orderByDesc('created_at')->orderByDesc('id'))
                ->filter(function (Builder $query) use ($search): void {
                    $this->search($query, $search);
                })
                ->addColumn('tanggal', fn (object $row): string => Carbon::parse($row->created_at)->format('d-m-Y'))
                ->addColumn('masuk', fn (object $row): int => (int) $row->jenis === 1 ? (int) $row->nominal : 0)
                ->addColumn('keluar', fn (object $row): int => (int) $row->jenis === 0 ? (int) $row->nominal : 0)
                ->withQuery('totals', fn (Builder $filtered): object => $this->totals($filtered))
                ->escapeColumns([])->toJson();
        }
        $oldestDate = (clone $base)->min('created_at');
        $oldestYear = $oldestDate ? Carbon::parse($oldestDate)->year : now()->year;
        $years = range(max(now()->year, $year), min($oldestYear, $year));
        $this->search($query, $search);
        $totals = $this->totals($query);

        return view('rekap.lain-lain', compact('cashType', 'month', 'year', 'search', 'years', 'totals') + [
            'periodLabel' => $period->locale('id')->translatedFormat('F Y'),
            'cashLabel' => $cashType === 1 ? 'Kas Besar PPN' : 'Kas Besar Non-PPN',
        ]);
    }

    private function search(Builder $query, string $search): void
    {
        if ($search !== '') {
            $query->where(function (Builder $query) use ($search): void {
                $query->where('uraian', 'like', '%'.$search.'%')->orWhere('nama_rek', 'like', '%'.$search.'%')
                    ->orWhere('no_rek', 'like', '%'.$search.'%')->orWhere('bank', 'like', '%'.$search.'%');
            });
        }
    }

    private function totals(Builder $query): object
    {
        return $query->cloneWithout(['columns', 'orders', 'limit', 'offset'])->selectRaw('COUNT(*) as count, COALESCE(SUM(CASE WHEN jenis = 1 THEN nominal ELSE 0 END), 0) as masuk, COALESCE(SUM(CASE WHEN jenis = 0 THEN nominal ELSE 0 END), 0) as keluar')->first();
    }
}
