<?php

namespace App\Http\Controllers;

use App\Models\db\Karyawan;
use App\Models\RekapGajiDetail;
use App\Services\EmployeePayrollService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PayrollCommissionController extends Controller
{
    public function preview(Request $request, EmployeePayrollService $payroll): JsonResponse
    {
        $data = $request->validate(['karyawan_id' => 'required|integer|exists:karyawans,id', 'periode' => 'required|date_format:Y-m']);
        $period = $payroll->nextPeriod();
        if (! $period || $period->format('Y-m') !== $data['periode']) {
            throw ValidationException::withMessages(['periode' => 'Periode gaji berubah. Muat ulang form untuk melihat rincian terbaru.']);
        }
        $employee = Karyawan::select(['id', 'jabatan_id', 'nama', 'persen_komisi'])->with('jabatan:id,nama')
            ->where('status', 1)->findOrFail($data['karyawan_id']);
        abort_unless($employee->jabatan?->isSales(), 404);
        $row = ['nama' => $employee->nama] + $payroll->commissions(collect([$employee]), $period)[$employee->id];

        return $this->response($row, false);
    }

    public function stored(RekapGajiDetail $detail): JsonResponse
    {
        abort_unless($detail->rincian_komisi, 404);

        return $this->response($detail->only(['nama', 'komisi', 'omset_komisi', 'persen_komisi', 'rincian_komisi']), true);
    }

    /** @param array<string, mixed> $row */
    private function response(array $row, bool $stored): JsonResponse
    {
        return response()->json(['title' => 'Komisi '.$row['nama'],
            'html' => view('rekap.gaji.commission-content', ['row' => $row, 'detail' => $row['rincian_komisi'], 'showAccounts' => $stored])->render()]);
    }
}
