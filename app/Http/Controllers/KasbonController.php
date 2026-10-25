<?php

namespace App\Http\Controllers;

use App\Models\db\Karyawan;
use App\Models\GroupWa;
use App\Models\KasBesar;
use App\Models\Kasbon;
use App\Models\PasswordKonfirmasi;
use App\Services\KasbonMessage;
use App\Services\KasbonService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class KasbonController extends Controller
{
    public function index(): View
    {
        return view('billing.kasbon.index');
    }

    public function report(Request $request): View
    {
        $filters = $request->validate(['jenis' => 'nullable|in:potong_gaji,cicilan', 'status' => 'nullable|in:berjalan,lunas,void,all',
            'karyawan_id' => 'nullable|integer|exists:karyawans,id', 'search' => 'nullable|string|max:100', 'bulan' => 'nullable|integer|between:1,12', 'tahun' => 'nullable|integer|between:2000,2155', 'print' => 'nullable|boolean']);
        $query = Kasbon::with('karyawan.jabatan')->orderByDesc('id');
        $status = $filters['status'] ?? (($filters['jenis'] ?? 'potong_gaji') === 'cicilan' ? 'berjalan' : 'all');
        $filters['bulan'] = $filters['bulan'] ?? now()->month;
        $filters['tahun'] = $filters['tahun'] ?? now()->year;
        $filters['jenis'] = $filters['jenis'] ?? 'potong_gaji';
        if ($filters['jenis'] === 'potong_gaji') {
            $query->whereMonth('tanggal', $filters['bulan'])->whereYear('tanggal', $filters['tahun']);
        }
        if ($status !== 'all') {
            $query->where('void', $status === 'void');
            if ($status !== 'void') {
                $query->where('lunas', $status === 'lunas');
            }
        }
        foreach (['jenis', 'karyawan_id'] as $field) {
            if (! empty($filters[$field])) {
                $query->where($field, $filters[$field]);
            }
        }
        if (! empty($filters['search'])) {
            $query->whereHas('karyawan', fn ($employees) => $employees->where('nama', 'like', '%'.$filters['search'].'%'));
        }
        $summary = (clone $query)->reorder()->selectRaw('COUNT(*) as jumlah, COALESCE(SUM(nominal),0) as nominal, COALESCE(SUM(total_bayar),0) as dibayar, COALESCE(SUM(sisa_kas),0) as sisa')->first();

        return view('rekap.kasbon.index', ['loans' => $query->paginate($request->boolean('print') ? max(1, (int) $summary->jumlah) : 25)->withQueryString(), 'summary' => $summary,
            'employees' => Karyawan::orderBy('nama')->get(['id', 'nama']), 'filters' => $filters, 'status' => $status]);
    }

    public function create(Request $request): View
    {
        $data = $request->validate(['jenis' => 'nullable|in:potong_gaji,cicilan']);
        $type = $data['jenis'] ?? 'potong_gaji';
        abort_if($type === 'cicilan' && ! in_array($request->user()->role, ['admin', 'su'], true), 403);
        $employees = Karyawan::with('jabatan')->where('status', 1)->orderBy('nama')->get();

        return view('billing.kasbon.create', ['employees' => $employees, 'type' => $type, 'token' => (string) Str::uuid(),
            'minimumPeriod' => app(KasbonService::class)->earliestSubmissionPeriod()]);
    }

    public function summary(Request $request, KasbonService $service): JsonResponse
    {
        $data = $request->validate(['karyawan_id' => 'required|integer|exists:karyawans,id', 'bulan' => 'required|integer|between:1,12', 'tahun' => 'required|integer|between:2000,2155']);
        $employee = Karyawan::with('jabatan')->findOrFail($data['karyawan_id']);
        $service->ensureEligible($employee);

        return response()->json($service->summary($employee, Carbon::create((int) $data['tahun'], (int) $data['bulan'], 1)));
    }

    public function store(Request $request, KasbonService $service): RedirectResponse
    {
        $data = $request->validate(['karyawan_id' => 'required|integer|exists:karyawans,id', 'jenis' => 'required|in:potong_gaji,cicilan',
            'nominal' => ['required', 'regex:/\A(?:[1-9][0-9]*|[1-9][0-9]{0,2}(?:\.[0-9]{3})+)\z/'],
            'cicil_kali' => 'required_if:jenis,cicilan|nullable|integer|min:1',
            'mulai_bulan' => 'required_if:jenis,cicilan|nullable|integer|between:1,12',
            'mulai_tahun' => 'required_if:jenis,cicilan|nullable|integer|between:2000,2155', 'submission_token' => 'required|uuid']);
        abort_if($data['jenis'] === 'cicilan' && ! in_array($request->user()->role, ['admin', 'su'], true), 403);
        $digits = str_replace('.', '', (string) $data['nominal']);
        if (strlen($digits) > 12) {
            throw ValidationException::withMessages(['nominal' => 'Nominal terlalu besar.']);
        }
        $data['nominal'] = (int) $digits;
        $loan = $service->create($data);
        if ($loan->wasRecentlyCreated) {
            $this->notify($loan, false);
        }

        return to_route('billing.kasbon.index')->with('success', 'Kasbon berhasil dicatat dan dana keluar dari Kas Besar PPN.');
    }

    public function cancel(Request $request, Kasbon $kasbon, KasbonService $service): RedirectResponse
    {
        $data = $request->validate(['alasan' => 'required|string|min:5|max:500', 'password' => 'required|string']);
        $confirmation = PasswordKonfirmasi::first();
        if (! $confirmation || ! hash_equals((string) $confirmation->password, $data['password'])) {
            throw ValidationException::withMessages(['password' => 'Password konfirmasi salah atau belum diatur.']);
        }
        $service->cancel($kasbon, $data['alasan']);
        $this->notify($kasbon->fresh(), true);

        return to_route('rekap.kasbon')->with('success', 'Sisa kasbon telah dikembalikan ke Kas Besar PPN. Riwayat tetap tersimpan.');
    }

    private function notify(Kasbon $loan, bool $returned): void
    {
        try {
            $group = GroupWa::where('untuk', 'kas-besar-ppn')->first();
            if (! $group) {
                return;
            }
            $cashId = $returned ? $loan->void_kas_besar_id : $loan->kas_besar_id;
            $cash = KasBesar::findOrFail($cashId);
            $message = app(KasbonMessage::class)->format($loan, $cash, $returned);
            $cash->sendWa($group->nama_group, $message);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}
