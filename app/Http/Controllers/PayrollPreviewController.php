<?php

namespace App\Http\Controllers;

use App\Services\EmployeePayrollService;
use App\Services\PayrollAttendanceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use OpenSpout\Common\Entity\Style\Style;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class PayrollPreviewController extends Controller
{
    private const COLUMNS = [
        'gaji_pokok' => 'Gaji Pokok', 'tunjangan_jabatan' => 'Tunjangan Jabatan', 'tunjangan_keluarga' => 'Tunjangan Keluarga',
        'bpjs_tk' => 'BPJS-TK Perusahaan', 'bpjs_k' => 'BPJS Kesehatan Perusahaan',
        'potongan_bpjs_tk' => 'Potongan BPJS-TK', 'potongan_bpjs_kesehatan' => 'Potongan BPJS Kesehatan',
        'komisi' => 'Komisi', 'tunjangan_bbm' => 'Tunjangan BBM', 'pendapatan_kotor' => 'Pendapatan Kotor',
        'pendapatan_bersih' => 'Pendapatan Bersih', 'potongan_kasbon' => 'Potongan Kasbon',
        'potongan_absensi' => 'Potongan Absensi', 'sisa_gaji_dibayar' => 'Sisa Gaji Dibayar',
    ];

    public function download(Request $request, string $format, EmployeePayrollService $payroll, PayrollAttendanceService $attendance): Response
    {
        $input = $request->validate(['periode' => ['required', 'date_format:Y-m']]);
        $period = $payroll->nextPeriod();
        if (! $period || $period->format('Y-m') !== $input['periode']) {
            throw ValidationException::withMessages(['periode' => 'Periode gaji berubah. Muat ulang form sebelum mengunduh preview.']);
        }
        $employees = $payroll->employees();
        if ($employees->isEmpty()) {
            throw ValidationException::withMessages(['pegawai' => 'Tidak ada pegawai aktif untuk preview gaji.']);
        }
        $attendance->clear();
        $rows = $payroll->previewRows($employees, $period);
        $snapshot = $attendance->enabled() ? $attendance->payload($attendance->period($period)) : null;
        $status = ! $attendance->enabled() ? 'Integrasi absensi nonaktif; potongan absensi dan BBM bernilai 0.'
            : (($snapshot['ready'] ?? false) && ($snapshot['source_employee_ids'] ?? []) === $attendance->sources($employees)
                ? 'Snapshot absensi siap; nilai tetap perlu ditinjau sebelum proses gaji.' : 'Absensi belum lengkap/siap. Potongan absensi dan BBM masih sementara atau belum tersedia.');
        if ($payroll->bpjs->setting($period->year) === null) {
            $status .= ' Dasar minimum BPJS Kesehatan tahun ini belum diatur; nilai BPJS belum final.';
        }
        $label = $period->copy()->locale('id')->translatedFormat('F Y');
        $range = $period->copy()->subMonth()->day(26)->format('d-m-Y').' s.d. '.$period->copy()->day(25)->format('d-m-Y');
        $filename = 'Preview_Gaji_IUP_'.$period->format('Y_m');
        $columns = self::COLUMNS;
        if ($format === 'pdf') {
            return Pdf::loadView('billing.form-cost-operational.form-gaji.preview-pdf', compact('rows', 'label', 'range', 'status', 'columns'))
                ->setPaper('a3', 'landscape')->download($filename.'.pdf')->header('Cache-Control', 'private, no-store');
        }
        $directory = 'private/payroll-previews';
        Storage::disk('local')->makeDirectory($directory);
        $path = tempnam(Storage::disk('local')->path($directory), 'preview-');
        if ($path === false) {
            throw new \RuntimeException('File preview belum dapat dibuat.');
        }
        chmod($path, 0600);
        try {
            $writer = SimpleExcelWriter::create($path, 'xlsx')->nameCurrentSheet('DRAFT Gaji');
            $writer->setHeaderStyle(new Style(fontBold: true, backgroundColor: 'DDEFE4'));
            $writer->addHeader(['No.', 'Nama Pegawai', 'Jabatan', ...array_values($columns)]);
            foreach ($rows as $index => $row) {
                $writer->addRow([$index + 1, $this->safeText($row['nama']), $this->safeText($row['jabatan']), ...array_map(fn (string $key): int => (int) ($row[$key] ?? 0), array_keys($columns))], new Style(format: '#,##0'));
            }
            $writer->addRow(['', 'TOTAL SELURUH PEGAWAI', '', ...array_map(fn (string $key): int => (int) $rows->sum($key), array_keys($columns))], new Style(fontBold: true, format: '#,##0'));
            $writer->addNewSheetAndMakeItCurrent('Informasi')->addHeader(['Keterangan', 'Nilai']);
            $writer->addRows([
                ['Status', 'DRAFT - belum diproses atau dibayarkan'], ['Periode', $label], ['Rentang absensi/komisi', $range],
                ['Kesiapan data', $status], ['Dibuat pada', now()->toIso8601String()],
                ['Sumber perhitungan', 'Service penggajian IUP yang sama dengan form. Unduhan tidak menarik ulang atau mengunci absensi.'],
            ]);
            $writer->close();

            return response()->download($path, $filename.'.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'Cache-Control' => 'private, no-store'])->deleteFileAfterSend(true);
        } catch (Throwable $exception) {
            if (is_file($path)) {
                unlink($path);
            }
            throw $exception;
        }
    }

    private function safeText(string $value): string
    {
        return preg_match('/\A[=+@\-\t\r\n]/', $value) ? "'".$value : $value;
    }
}
