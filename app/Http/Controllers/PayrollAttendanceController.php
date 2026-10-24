<?php

namespace App\Http\Controllers;

use App\Models\RekapGajiDetail;
use App\Services\EmployeePayrollService;
use App\Services\PayrollAttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PayrollAttendanceController extends Controller
{
    public function refresh(Request $request, EmployeePayrollService $payroll, PayrollAttendanceService $attendance): RedirectResponse
    {
        $data = $request->validate(['periode' => 'required|date_format:Y-m']);
        $period = $payroll->nextPeriod();
        if (! $period || $period->format('Y-m') !== $data['periode']) {
            throw ValidationException::withMessages(['attendance' => 'Periode gaji berubah. Muat ulang form.']);
        }
        abort_unless($attendance->enabled(), 422, 'Integrasi absensi sedang dinonaktifkan.');
        $employees = $payroll->employees();
        abort_if($employees->isEmpty(), 422, 'Tidak ada pegawai aktif.');
        $attendance->refresh($period, $attendance->sources($employees));

        return redirect()->route('billing.form-cost-operational.gaji')->with('success', 'Data absensi diperbarui. Periksa potongan dan BBM sebelum memproses gaji.');
    }

    public function preview(Request $request, EmployeePayrollService $payroll, PayrollAttendanceService $attendance): JsonResponse
    {
        $data = $request->validate(['karyawan_id' => 'required|integer', 'periode' => 'required|date_format:Y-m']);
        $period = $payroll->nextPeriod();
        abort_unless($period && $period->format('Y-m') === $data['periode'], 422, 'Periode gaji berubah. Muat ulang form.');
        $employee = $payroll->employees()->firstWhere('id', $data['karyawan_id']);
        abort_unless($employee, 404);
        $detail = $attendance->calculate($employee, $period)['rincian_absensi'];
        abort_unless($detail, 404);

        return $this->response($employee->nama, $detail, false);
    }

    public function stored(RekapGajiDetail $detail): JsonResponse
    {
        abort_unless($detail->rincian_absensi, 404);

        return $this->response($detail->nama, $detail->rincian_absensi, true);
    }

    private function response(string $name, array $detail, bool $stored): JsonResponse
    {
        return response()->json(['title' => ($detail['kind'] === 'bbm' ? 'Tunjangan BBM ' : 'Potongan absensi ').$name,
            'html' => view('rekap.gaji.attendance-content', compact('detail', 'stored'))->render()])->header('Cache-Control', 'no-store, private');
    }
}
