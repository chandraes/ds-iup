<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockTableRequest;
use App\Models\db\Barang\Barang;
use App\Models\db\Barang\BarangStokHarga;
use App\Models\db\Karyawan;
use App\Models\db\Pajak;
use App\Services\StockMutationService;
use App\Services\StockTableService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class AdminStockController extends Controller
{
    public function index(StockTableRequest $request, StockTableService $table): View
    {
        if ($request->input('mode') === 'classic') {
            return app(BarangController::class)->stok_all($request);
        }
        $filters = $request->safe()->only(['unit', 'type', 'kategori', 'barang_nama', 'filter_harga']);
        $assistant = $request->user()->role === 'asisten-admin';
        if ($assistant) {
            $filters['filter_harga'] = 1;
        }

        $stockConfig = ['assistant' => $assistant, 'superuser' => $request->user()->role === 'su', 'filters' => $filters,
            'data' => route('db.stok-all.data'), 'detail' => route('db.stok-all.detail', ':id'), 'history' => route('db.stok-all.history', ':id'),
            'employees' => route('db.stok-all.employees'), 'actions' => collect(['price', 'propose', 'approve', 'correct', 'hide', 'loss'])
                ->mapWithKeys(fn ($action) => [$action => route('db.stok-all.'.$action, ':id')])->all()];

        return view('db.stok-all.index', $table->filterOptions($filters) + compact('filters', 'assistant', 'stockConfig'));
    }

    public function data(StockTableRequest $request, StockTableService $table): JsonResponse
    {
        $filters = $request->safe()->only(['unit', 'type', 'kategori', 'barang_nama', 'filter_harga']);
        if ($request->user()->role === 'asisten-admin') {
            $filters['filter_harga'] = 1;
        }
        $search = (string) $request->input('search.value', '');
        $request->merge(['start' => $request->input('start', 0), 'length' => $request->input('length', 50), 'columns' => [], 'order' => []]);

        return DB::transaction(function () use ($filters, $search, $table): JsonResponse {
            $rate = (float) Pajak::where('untuk', 'ppn')->firstOrFail()->persen;
            $query = $table->adminQuery($filters, $rate);
            $totalsQuery = clone $query;
            $table->search($totalsQuery, $search);
            $totals = $table->totals($totalsQuery);

            return DataTables::of($query)->filter(fn ($q) => $table->search($q, $search))->order(fn ($q) => $table->order($q))
                ->with('totals', $totals)->toJson()->header('Cache-Control', 'no-store, private');
        });
    }

    public function detail(Request $request, int $stock, StockMutationService $mutations): JsonResponse
    {
        $item = BarangStokHarga::with(['barang.satuan', 'barang_nama', 'harga_temp' => fn ($q) => $q->where('status', 1)])->where('hide', 0)->findOrFail($stock);
        if ($request->user()->role === 'asisten-admin') {
            abort_unless(BarangStokHarga::where('barang_id', $item->barang_id)->where('harga', 0)->exists(), 403);
        }
        $rate = (float) Pajak::where('untuk', 'ppn')->firstOrFail()->persen;

        return response()->json(['id' => $item->id, 'barang_id' => $item->barang_id, 'nama' => $item->barang_nama?->nama ?? '-',
            'kode' => $item->barang?->kode ?? '-', 'merk' => $item->barang?->merk ?? '-', 'satuan' => $item->barang?->satuan?->nama ?? '-',
            'stok' => $item->stok, 'stok_awal' => $item->stok_awal, 'harga' => $item->harga, 'harga_beli' => $item->harga_beli,
            'min_jual' => $item->min_jual, 'harga_ajuan' => $item->harga_temp?->harga_ajuan, 'min_jual_ajuan' => $item->harga_temp?->min_jual_ajuan,
            'loss_unit_price' => (int) round($item->harga_beli * (1 + $rate / 100)), 'revision' => $mutations->revision($item),
            'permissions' => $mutations->permissions($request->user(), $item)])->header('Cache-Control', 'no-store, private');
    }

    public function history(Request $request, int $product): JsonResponse
    {
        $input = $request->validate(['start' => ['nullable', 'integer', 'between:0,2147483647']]);
        $item = Barang::with(['barang_nama:id,nama', 'satuan:id,nama'])->findOrFail($product);
        $rows = BarangStokHarga::where('barang_id', $product)->select('id', 'stok_awal', 'harga_beli', 'harga', 'created_at')
            ->orderByDesc('created_at')->orderByDesc('id')->skip($input['start'] ?? 0)->take(51)->get();

        return response()->json(['nama' => $item->barang_nama?->nama ?? '-', 'kode' => $item->kode, 'merk' => $item->merk,
            'keterangan' => $item->keterangan, 'satuan' => $item->satuan?->nama ?? '-', 'data' => $rows->take(50)->map(fn ($row): array => [
                'id' => $row->id, 'stok_awal' => $row->stok_awal, 'harga_beli' => $row->harga_beli, 'harga' => $row->harga,
                'tanggal' => $row->created_at?->format('d/m/Y'),
            ])->values(), 'more' => $rows->count() > 50])->header('Cache-Control', 'no-store, private');
    }

    public function employees(): JsonResponse
    {
        return response()->json(Karyawan::where('status', 1)->select('id', 'nama')->orderBy('nama')->get())->header('Cache-Control', 'no-store, private');
    }

    public function mutate(Request $request, int $stock, string $action, StockMutationService $mutations): JsonResponse
    {
        abort_unless(in_array($request->user()->role, $action === 'propose' ? ['su', 'admin', 'asisten-admin'] : ['su', 'admin'], true), 403);
        abort_if($action === 'correct' && $request->user()->role !== 'su', 403);
        $number = ['required', 'integer', 'between:0,2147483647'];
        $positive = ['required', 'integer', 'between:1,2147483647'];
        $rules = match ($action) {
            'price' => ['harga' => $number, 'min_jual' => $positive],
            'propose', 'approve' => ['harga_ajuan' => $number, 'min_jual_ajuan' => $positive],
            'correct' => ['stok_awal' => $number, 'stok' => $number, 'harga_beli' => ['nullable', 'integer', 'between:0,2147483647']],
            'loss' => ['jumlah_hilang' => $positive, 'aksi' => ['required', Rule::in([1, 2])],
                'karyawan_id' => ['required', 'integer', Rule::exists('karyawans', 'id')->where('status', 1)]],
            'hide' => [],
        };
        $input = $request->validate($rules + ['revision' => ['required', 'string', 'size:64']]);
        $revision = $input['revision'];
        unset($input['revision']);
        $result = $mutations->mutate($request->user(), $stock, $action, $input, $revision);

        return response()->json($result, $result['status'] === 'success' ? 200 : 422);
    }
}
