<?php

namespace App\Http\Controllers;

use App\Models\db\Karyawan;
use App\Models\RekapGaji;
use App\Models\RekapGajiDetail;
use App\Services\EmployeePayrollService;
use App\Services\PayrollAttendanceService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SalesPayrollController extends Controller
{
    public function index(Request $request, EmployeePayrollService $payroll, PayrollAttendanceService $attendance): Response
    {
        $employeeId = (int) $request->user()->karyawan_id;
        abort_unless($employeeId, 403, 'Akun sales belum terhubung dengan pegawai.');
        $employee = Karyawan::with('jabatan')->findOrFail($employeeId);
        $filters = $request->validate(['year' => ['nullable', 'integer', 'between:1901,2155'], 'month' => ['nullable', 'integer', 'between:1,12']]);
        $activePeriod = now()->startOfMonth();
        if (now()->day >= 26) {
            $activePeriod->addMonth();
        }
        $year = (int) ($filters['year'] ?? $activePeriod->year);
        $selectedMonth = (int) ($filters['month'] ?? ($year === $activePeriod->year ? $activePeriod->month : 1));
        $period = Carbon::create($year, $selectedMonth, 1)->startOfDay();
        $records = RekapGajiDetail::where('karyawan_id', $employeeId)
            ->whereHas('rekap_gaji', fn ($query) => $query->where('tahun', $year))->with('rekap_gaji:id,bulan,tahun,periode_mulai,periode_selesai')->get()
            ->mapWithKeys(function (RekapGajiDetail $detail): array {
                $row = $detail->toArray();
                $row['legacy_payable'] = $detail->sisa_gaji_dibayar === null;
                $row['sisa_gaji_dibayar'] = $detail->sisa_gaji_dibayar ?? $detail->pendapatan_bersih;

                return [(int) $detail->rekap_gaji->bulan => $row];
            });
        $processed = RekapGaji::where('tahun', $year)->pluck('bulan')->map(fn ($month): int => (int) $month)->all();
        $months = collect(range(1, 12))->map(function (int $month) use ($year, $records, $processed, $employee, $payroll, $attendance): array {
            $date = Carbon::create($year, $month, 1)->startOfDay();
            $from = $date->copy()->subMonth()->day(26);
            $status = $records->has($month) ? 'stored' : (in_array($month, $processed, true) ? 'not_recorded'
                : ($from->isFuture() ? 'upcoming' : ((! $employee->status || ! $employee->jabatan?->isSales()) ? 'unavailable' : 'estimate')));

            $amounts = $records->get($month);
            if ($status === 'estimate') {
                $amounts = ['nama' => $employee->nama, 'gaji_pokok' => $employee->gaji_pokok, 'tunjangan_jabatan' => $employee->tunjangan_jabatan,
                    'tunjangan_keluarga' => $employee->tunjangan_keluarga] + $payroll->amounts($employee, $date);
                $attendance->clear();
            }
            $bbmAvailable = $status === 'stored' || ($attendance->enabled() && $amounts && $amounts['rincian_absensi'] !== null);

            return ['month' => $month, 'label' => $date->copy()->locale('id')->monthName, 'from' => $from, 'to' => $date->copy()->day(25),
                'status' => $status, 'record' => $records->get($month), 'amounts' => $amounts, 'bbmAvailable' => $bbmAvailable, 'attendanceEnabled' => $attendance->enabled()];
        });
        $selected = $months->firstWhere('month', $selectedMonth);
        $isStored = $selected['status'] === 'stored';
        $row = $selected['amounts'];
        $bbmAvailable = $selected['bbmAvailable'];
        $attendanceMessage = $isStored ? 'Snapshot final' : (! $attendance->enabled() ? 'Integrasi absensi nonaktif' : (! $bbmAvailable ? 'Snapshot absensi belum tersedia' : 'Snapshot absensi terakhir dari admin'));
        $years = RekapGaji::whereHas('details', fn ($query) => $query->where('karyawan_id', $employeeId))->pluck('tahun')
            ->map(fn ($value): int => (int) $value)->merge(range(now()->year - 3, now()->year + 1))->push($year)->unique()->sortDesc()->values();

        return response()->view('sales.gaji.index', compact('employee', 'year', 'selectedMonth', 'period', 'months', 'selected', 'row', 'isStored', 'bbmAvailable', 'attendanceMessage', 'years'))->header('Cache-Control', 'no-store, private');
    }

    public function calculation(Request $request, EmployeePayrollService $payroll, PayrollAttendanceService $attendance): JsonResponse
    {
        $employeeId = (int) $request->user()->karyawan_id;
        abort_unless($employeeId, 403);
        $input = $request->validate(['year' => ['required', 'integer', 'between:1901,2155'], 'month' => ['required', 'integer', 'between:1,12'], 'type' => ['required', 'in:komisi,bbm']]);
        $employee = Karyawan::with('jabatan')->findOrFail($employeeId);
        $period = Carbon::create((int) $input['year'], (int) $input['month'], 1)->startOfDay();
        $record = RekapGajiDetail::where('karyawan_id', $employeeId)->whereHas('rekap_gaji', fn ($query) => $query->where('tahun', $period->year)->where('bulan', $period->month))->first();
        $stored = $record !== null;
        if (! $stored) {
            abort_if(RekapGaji::where('tahun', $period->year)->where('bulan', $period->month)->exists(), 404);
            abort_unless($employee->status && $employee->jabatan?->isSales() && $period->copy()->subMonth()->day(26)->lte(now()), 404);
        }
        if ($input['type'] === 'komisi') {
            $row = $stored ? $record->toArray() : ['nama' => $employee->nama] + $payroll->commissions(collect([$employee]), $period)[$employeeId];
            $detail = $row['rincian_komisi'];
            $html = $detail ? view('rekap.gaji.commission-content', ['row' => $row, 'detail' => $detail, 'showAccounts' => $stored, 'turnoverDetailRoute' => 'sales.omset-harian.detail'])->render()
                : '<p class="text-secondary">Rincian komisi tidak tersimpan untuk periode ini. Nominal komisi tetap mengikuti rekap final.</p>';
        } else {
            $detail = $stored ? $record->rincian_absensi : $attendance->calculate($employee, $period)['rincian_absensi'];
            $html = $detail ? view('rekap.gaji.attendance-content', ['detail' => $detail, 'stored' => $stored, 'readOnlySales' => true])->render()
                : '<p class="text-secondary">'.($stored ? 'Rincian BBM tidak tersimpan untuk periode ini. Nominal tetap mengikuti rekap final.' : ($attendance->enabled() ? 'Snapshot absensi belum tersedia' : 'Integrasi absensi nonaktif')).'</p>';
        }

        return response()->json(['title' => ($input['type'] === 'komisi' ? 'Komisi ' : 'Tunjangan BBM ').$period->locale('id')->translatedFormat('F Y').' · '.($stored ? 'Snapshot final' : 'Estimasi'), 'html' => $html])->header('Cache-Control', 'no-store, private');
    }
}
