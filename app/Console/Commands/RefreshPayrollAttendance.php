<?php

namespace App\Console\Commands;

use App\Services\EmployeePayrollService;
use App\Services\PayrollAttendanceService;
use Illuminate\Console\Command;
use Throwable;

class RefreshPayrollAttendance extends Command
{
    protected $signature = 'attendance:refresh-payroll';

    protected $description = 'Perbarui snapshot periode form gaji yang belum terkunci sesuai interval dan retry';

    public function handle(EmployeePayrollService $payroll, PayrollAttendanceService $attendance): int
    {
        if (! $attendance->enabled() || ! config('attendance.payroll_auto_sync')) {
            $this->info('Pembaruan otomatis nonaktif.');

            return self::SUCCESS;
        }
        $period = $payroll->nextPeriod();
        if (! $period) {
            $this->info('Tidak ada periode form gaji yang terbuka.');

            return self::SUCCESS;
        }
        $employees = $payroll->employees();
        if ($employees->isEmpty()) {
            $this->info('Tidak ada pegawai aktif.');

            return self::SUCCESS;
        }
        try {
            $refreshed = $attendance->refresh($period, $attendance->sources($employees), 'automatic');
            $this->info($refreshed ? 'Snapshot '.$period->format('Y-m').' diperbarui; gaji tidak diproses.' : 'Dilewati: belum waktunya, sedang diperbarui, atau periode terkunci.');

            return self::SUCCESS;
        } catch (Throwable) {
            $this->error('Snapshot belum berhasil diperbarui. Lihat status pada form gaji dan log aplikasi; retry dijadwalkan.');

            return self::FAILURE;
        }
    }
}
