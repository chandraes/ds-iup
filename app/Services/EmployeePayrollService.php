<?php

namespace App\Services;

use App\Models\db\Karyawan;
use App\Models\KasBesar;
use App\Models\RekapGaji;
use App\Models\RekapGajiDetail;
use App\Models\transaksi\InvoiceJual;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmployeePayrollService
{
    public function __construct(private KasbonService $kasbon, private PayrollAttendanceService $attendance, public BpjsHealthMinimumService $bpjs) {}

    /** @return \Illuminate\Database\Eloquent\Collection<int, Karyawan> */
    public function employees(bool $lock = false): \Illuminate\Database\Eloquent\Collection
    {
        $query = Karyawan::select(['id', 'jabatan_id', 'nomor', 'nama', 'gaji_pokok', 'tunjangan_jabatan', 'tunjangan_keluarga',
            'apa_bpjs_tk', 'apa_bpjs_kes', 'bank', 'no_rek', 'nama_rek', 'persen_komisi', 'tunjangan_bbm'])
            ->with('jabatan:id,nama')->where('status', 1)->orderBy('id');
        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->get();
    }

    /** @return Collection<int, array<string, mixed>> */
    public function sortRows(Collection $rows): Collection
    {
        return $rows->sort(function (array $left, array $right): int {
            $leftJob = mb_strtolower(trim($left['jabatan']));
            $rightJob = mb_strtolower(trim($right['jabatan']));

            return [$leftJob === 'sales' ? 1 : 0, $leftJob, mb_strtolower($left['nama']), $left['nik']]
                <=> [$rightJob === 'sales' ? 1 : 0, $rightJob, mb_strtolower($right['nama']), $right['nik']];
        })->values();
    }

    public function processingStartsAt(Carbon $period): Carbon
    {
        return $period->copy()->timezone('Asia/Jakarta')->startOfMonth()->day(26)->startOfDay();
    }

    public function canProcess(Carbon $period): bool
    {
        return Carbon::now('Asia/Jakarta')->greaterThanOrEqualTo($this->processingStartsAt($period));
    }

    public function assertProcessable(Carbon $period): void
    {
        if (! $this->canProcess($period)) {
            throw ValidationException::withMessages(['total' => 'Gaji periode '.$period->copy()->locale('id')->translatedFormat('F Y').' baru dapat diproses mulai '.$this->processingStartsAt($period)->locale('id')->translatedFormat('d F Y').' pukul 00.00 WIB. Preview tetap dapat dilihat.']);
        }
    }

    public function nextPeriod(bool $lock = false): ?Carbon
    {
        $query = RekapGaji::orderBy('tahun')->orderBy('bulan');
        if ($lock) {
            $query->lockForUpdate();
        }
        $records = $query->get(['bulan', 'tahun']);
        $current = now()->startOfMonth();
        $previewLimit = $current->copy()->addMonth();
        if ($records->isEmpty()) {
            return $current;
        }
        $paid = $records->mapWithKeys(fn (RekapGaji $record): array => [sprintf('%04d-%02d', $record->tahun, $record->bulan) => true]);
        $first = $records->first();
        $period = Carbon::create((int) $first->tahun, (int) $first->bulan, 1)->startOfDay();
        while ($period->lte($previewLimit)) {
            if (! $paid->has($period->format('Y-m'))) {
                return $period;
            }
            $period->addMonth();
        }

        return null;
    }

    /** @return array<int, array<string, mixed>> */
    public function commissions(Collection $employees, Carbon $period): array
    {
        $sales = $employees->filter(fn (Karyawan $employee): bool => $employee->jabatan?->isSales() ?? false);
        $start = $period->copy()->startOfMonth()->subMonth()->day(26);
        $end = $period->copy()->startOfMonth()->day(26);
        $daily = (new InvoiceJual)->salesTurnoverByDay($start, $end, $sales->pluck('id')->all(), paidOnly: true);
        $commissions = [];
        foreach ($sales as $employee) {
            $days = [];
            for ($date = $start->copy(); $date->lt($end); $date->addDay()) {
                $days[] = ['tanggal' => $date->toDateString()] + ($daily[$employee->id][$date->toDateString()] ?? ['penjualan' => 0, 'void' => 0, 'omset' => 0]);
            }
            $turnover = array_sum(array_column($days, 'omset'));
            $rate = (int) round((float) $employee->persen_komisi * 100);
            $commission = intdiv($turnover, 10000) * $rate + (int) round(($turnover % 10000) * $rate / 10000);
            $commissions[$employee->id] = ['omset_komisi' => $turnover, 'persen_komisi' => number_format($rate / 100, 2, '.', ''), 'komisi' => $commission,
                'rincian_komisi' => ['mode' => 'paid', 'karyawan_id' => $employee->id, 'periode_mulai' => $start->toDateString(), 'periode_selesai' => $end->copy()->subDay()->toDateString(),
                    'penjualan' => array_sum(array_column($days, 'penjualan')), 'void' => array_sum(array_column($days, 'void')), 'harian' => $days]];
        }

        return $commissions;
    }

    /** @return Collection<int, array<string, mixed>> */
    public function previewRows(Collection $employees, Carbon $period): Collection
    {
        $commissions = $this->commissions($employees, $period);
        $payments = $this->kasbon->paymentsForEmployees($employees->pluck('id')->all(), $period);

        return $this->sortRows($employees->map(fn (Karyawan $employee): array => array_merge([
            'karyawan_id' => $employee->id, 'nik' => ($employee->kode ?? '').sprintf('%03d', $employee->nomor), 'nama' => $employee->nama,
            'jabatan' => $employee->jabatan?->nama ?? '—', 'gaji_pokok' => $employee->gaji_pokok,
            'tunjangan_jabatan' => $employee->tunjangan_jabatan, 'tunjangan_keluarga' => $employee->tunjangan_keluarga,
        ], $this->amounts($employee, $period, commission: $commissions[$employee->id] ?? [], payments: $payments[$employee->id] ?? []))));
    }

    /** @return array<string, mixed> */
    public function amounts(Karyawan $employee, Carbon $period, bool $lock = false, ?array $commission = null, ?array $payments = null): array
    {
        $commission ??= $this->commissions(collect([$employee]), $period)[$employee->id] ?? [];
        $commissionAmount = $commission['komisi'] ?? 0;
        $attendance = $this->attendance->calculate($employee, $period);
        $base = (int) $employee->gaji_pokok;
        $bpjsBase = $base + (int) $employee->tunjangan_jabatan + (int) $employee->tunjangan_keluarga;
        $healthInsuranceBase = $this->bpjs->base($bpjsBase, $period->year);
        $tk = $employee->apa_bpjs_tk ? (int) round($bpjsBase * 0.0489) : 0;
        $kes = $employee->apa_bpjs_kes ? (int) round($healthInsuranceBase * 0.04) : 0;
        $potTk = $employee->apa_bpjs_tk ? (int) round($bpjsBase * 0.02) : 0;
        $potKes = $employee->apa_bpjs_kes ? (int) round($healthInsuranceBase * 0.01) : 0;
        $income = $bpjsBase + $commissionAmount + $attendance['tunjangan_bbm'];
        $net = $income - $potTk - $potKes;
        $payments ??= $this->kasbon->payments($employee, $period, $lock);
        $deduction = array_sum(array_column($payments, 'amount'));

        return array_merge(['komisi' => 0, 'omset_komisi' => null, 'persen_komisi' => null, 'rincian_komisi' => null, 'tunjangan_bbm' => 0, 'bpjs_tk' => $tk, 'bpjs_k' => $kes, 'potongan_bpjs_tk' => $potTk, 'potongan_bpjs_kesehatan' => $potKes,
            'pendapatan_kotor' => $income + $tk + $kes, 'pendapatan_bersih' => $net,
            'potongan_kasbon' => $deduction, 'sisa_gaji_dibayar' => $net - $deduction - $attendance['potongan_absensi']], $commission, $attendance);
    }

    public function store(?string $expectedPeriod = null, ?string $attendanceRevision = null): RekapGaji
    {
        $this->attendance->clear();
        $bpjsPeriod = $this->nextPeriod();
        if (! $bpjsPeriod) {
            throw ValidationException::withMessages(['total' => 'Seluruh periode gaji sudah diproses.']);
        }
        $this->assertProcessable($bpjsPeriod);
        $this->bpjs->requireConfigured($bpjsPeriod->year);
        $attendancePeriod = null;
        if ($this->attendance->enabled()) {
            $employees = $this->employees();
            $period = $this->nextPeriod();
            if (! $period || $expectedPeriod !== $period->format('Y-m') || $employees->isEmpty()) {
                throw ValidationException::withMessages(['total' => 'Periode/pegawai gaji berubah. Muat ulang form.']);
            }
            $this->attendance->assertReady($period, $this->attendance->sources($employees), $attendanceRevision);
            $rows = $this->previewRows($employees, $period);
            if ($rows->contains(fn (array $row): bool => $row['sisa_gaji_dibayar'] < 0)) {
                throw ValidationException::withMessages(['total' => 'Potongan melebihi gaji bersih. Periksa kembali rincian pegawai.']);
            }
            if ((KasBesar::where('ppn_kas', 1)->latest('id')->value('saldo') ?? 0) < $rows->sum('sisa_gaji_dibayar')) {
                throw ValidationException::withMessages(['total' => 'Saldo Kas Besar PPN tidak mencukupi.']);
            }
            $attendancePeriod = $this->attendance->lock($period, $this->attendance->sources($employees), $attendanceRevision);
        }

        return DB::transaction(function () use ($expectedPeriod, $attendanceRevision, $attendancePeriod): RekapGaji {
            $employees = $this->employees(true);
            if ($employees->isEmpty()) {
                throw ValidationException::withMessages(['total' => 'Tidak ada pegawai aktif.']);
            }
            $period = $this->nextPeriod(true);
            if (! $period || ($expectedPeriod !== null && $expectedPeriod !== $period->format('Y-m'))) {
                throw ValidationException::withMessages(['total' => 'Periode gaji sudah diproses atau berubah. Muat ulang form sebelum melanjutkan.']);
            }
            $this->assertProcessable($period);
            $this->bpjs->requireConfigured($period->year, true);
            if ($attendancePeriod) {
                DB::table('payroll_attendance_periods')->where('id', $attendancePeriod->id)->lockForUpdate()->first();
                $this->attendance->clear();
                $this->attendance->assertReady($period, $this->attendance->sources($employees), $attendanceRevision);
            }
            $cash = KasBesar::where('ppn_kas', 1)->orderByDesc('id')->lockForUpdate()->first();
            $rekap = RekapGaji::create(['bulan' => $period->month, 'tahun' => $period->year,
                'periode_mulai' => $period->copy()->subMonth()->day(26)->toDateString(),
                'periode_selesai' => $period->copy()->day(25)->toDateString(),
                'uraian' => 'Gaji Bulan '.$period->copy()->locale('id')->translatedFormat('F Y'), 'total' => 0]);
            $total = 0;
            $commissions = $this->commissions($employees, $period);
            $payments = $this->kasbon->paymentsForEmployees($employees->pluck('id')->all(), $period, true);
            foreach ($employees as $employee) {
                $employeePayments = $payments[$employee->id] ?? [];
                $amounts = $this->amounts($employee, $period, true, $commissions[$employee->id] ?? [], $employeePayments);
                if ($amounts['sisa_gaji_dibayar'] < 0) {
                    throw ValidationException::withMessages(['total' => 'Potongan melebihi gaji bersih '.$employee->nama.'.']);
                }
                RekapGajiDetail::create(array_merge($amounts, ['rekap_gaji_id' => $rekap->id,
                    'karyawan_id' => $employee->id, 'nik' => ($employee->kode ?? '').sprintf('%03d', $employee->nomor), 'nama' => $employee->nama,
                    'jabatan' => $employee->jabatan?->nama ?? '—', 'gaji_pokok' => $employee->gaji_pokok,
                    'tunjangan_jabatan' => $employee->tunjangan_jabatan, 'tunjangan_keluarga' => $employee->tunjangan_keluarga,
                    'bank' => $employee->bank, 'no_rek' => $employee->no_rek, 'nama_rek' => $employee->nama_rek]));
                foreach ($employeePayments as $payment) {
                    $loan = $payment['loan'];
                    $remaining = (int) $loan->sisa_kas - $payment['amount'];
                    $loan->update(['sisa_kas' => $remaining, 'total_bayar' => $loan->total_bayar + $payment['amount'], 'lunas' => $remaining === 0]);
                }
                $total += $amounts['sisa_gaji_dibayar'];
            }
            if (! $cash || $cash->saldo < $total) {
                throw ValidationException::withMessages(['total' => 'Saldo Kas Besar PPN tidak mencukupi.']);
            }
            $rekap->update(['total' => $total]);
            KasBesar::create(['ppn_kas' => 1, 'jenis' => 0, 'uraian' => $rekap->uraian, 'nominal' => $total,
                'saldo' => $cash->saldo - $total, 'modal_investor_terakhir' => $cash->modal_investor_terakhir,
                'nama_rek' => 'Masing-masing karyawan', 'bank' => '-', 'no_rek' => '-']);

            $this->attendance->seal($attendancePeriod, $rekap->id);

            return $rekap;
        }, 3);
    }
}
