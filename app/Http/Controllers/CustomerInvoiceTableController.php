<?php

namespace App\Http\Controllers;

use App\Services\CustomerInvoiceTableService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class CustomerInvoiceTableController extends Controller
{
    public function data(Request $request, CustomerInvoiceTableService $table): JsonResponse
    {
        $input = $request->validate([
            'expired' => ['nullable', 'in:yes,no'], 'apa_ppn' => ['nullable', 'in:yes,no'],
            'karyawan_id' => ['nullable', 'integer', 'min:1'], 'konsumen_id' => ['nullable', 'integer', 'min:1'],
            'kecamatan_id' => ['nullable', 'string', 'max:255'], 'kabupaten_id' => ['nullable', 'string', 'max:255'],
            'draw' => ['nullable', 'integer', 'between:0,2147483647'], 'start' => ['nullable', 'integer', 'between:0,2147483647'],
            'length' => ['nullable', 'integer', 'between:1,500'], 'search' => ['nullable', 'array'],
            'search.value' => ['nullable', 'string', 'max:100'], 'order' => ['nullable', 'array', 'max:3'],
            'order.*.column' => ['required', 'integer', 'between:0,15'], 'order.*.dir' => ['required', 'in:asc,desc'],
        ]);
        $filters = array_intersect_key($input, array_flip(['expired', 'apa_ppn', 'karyawan_id', 'konsumen_id', 'kecamatan_id', 'kabupaten_id']));
        $search = (string) ($input['search']['value'] ?? '');
        $orders = $input['order'] ?? [];
        $request->merge(['start' => $input['start'] ?? 0, 'length' => $input['length'] ?? 50, 'columns' => [], 'order' => []]);

        return DB::transaction(function () use ($filters, $search, $orders, $table): JsonResponse {
            $query = $table->query($filters);
            $totalsQuery = clone $query;
            $table->search($totalsQuery, $search);
            $totals = $table->totals($totalsQuery);
            $data = DataTables::of($query)->filter(fn ($query) => $table->search($query, $search))
                ->order(fn ($query) => $table->order($query, $orders))
                ->addColumn('tanggal_en', fn ($row): ?string => $row->created_at ? Carbon::parse($row->created_at)->toDateString() : null)
                ->addColumn('full_kode', fn ($row): string => 'K'.str_pad((string) $row->konsumen_kode, 2, '0', STR_PAD_LEFT))
                ->with('totals', $totals);
            foreach (['id', 'karyawan_id', 'konsumen_id', 'konsumen_kode', 'plafon', 'nomor', 'total', 'diskon', 'ppn', 'add_fee',
                'grand_total', 'dp', 'dp_ppn', 'cicilan', 'cicilan_count', 'sisa_ppn', 'sisa_tagihan', 'ppn_dipungut', 'kas_ppn'] as $column) {
                $data->editColumn($column, fn ($row): ?int => $row->{$column} === null ? null : (int) $row->{$column});
            }

            return $data->toJson()->header('Cache-Control', 'no-store, private');
        });
    }

    public function paymentData(int $invoice, CustomerInvoiceTableService $table): JsonResponse
    {
        $row = $table->query()->where('i.id', $invoice)->first();
        abort_unless($row, 404);

        return response()->json(['id' => (int) $row->id, 'kode' => $row->kode, 'kas_ppn' => (int) $row->kas_ppn,
            'sisa_tagihan' => (int) $row->sisa_tagihan, 'sisa_ppn' => (int) $row->sisa_ppn, 'ppn_dipungut' => (int) $row->ppn_dipungut,
            'konsumen' => ['nama' => $row->konsumen_nama ?? '', 'kode_toko' => ['kode' => $row->kode_toko ?? '']],
            'nf_sisa_tagihan' => number_format($row->sisa_tagihan, 0, ',', '.'), 'nf_sisa_ppn' => number_format($row->sisa_ppn, 0, ',', '.')])
            ->header('Cache-Control', 'no-store, private');
    }

    public function history(Request $request, int $invoice, CustomerInvoiceTableService $table): JsonResponse
    {
        $input = $request->validate(['start' => ['nullable', 'integer', 'between:0,2147483647']]);
        $start = (int) ($input['start'] ?? 0);
        $row = $table->query()->where('i.id', $invoice)->first();
        abort_unless($row, 404);
        $payments = DB::table('invoice_jual_cicils')->where('invoice_jual_id', $invoice)->orderBy('id')->skip($start)->take(51)
            ->get(['id', 'created_at', 'nominal', 'ppn']);

        return response()->json(['id' => (int) $row->id, 'kode' => $row->kode, 'kas_ppn' => (int) $row->kas_ppn,
            'data' => $payments->take(50)->map(fn ($payment): array => ['id' => (int) $payment->id,
                'tanggal' => $payment->created_at ? Carbon::parse($payment->created_at)->format('d-m-Y') : '',
                'nominal' => (int) $payment->nominal, 'ppn' => (int) $payment->ppn, 'total' => (int) $payment->nominal + (int) $payment->ppn])->values(),
            'more' => $payments->count() > 50, 'next_start' => $start + min(50, $payments->count())])
            ->header('Cache-Control', 'no-store, private');
    }
}
