<?php

namespace App\Services;

use App\Models\db\Karyawan;
use App\Models\RekapGaji;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PayrollAttendanceService
{
    private array $periods = [];

    private array $people = [];

    public function enabled(): bool
    {
        return (bool) config('attendance.enabled', false);
    }

    public function clear(): void
    {
        $this->periods = [];
        $this->people = [];
    }

    public function sources(Collection $employees): array
    {
        $ids = $employees->map(fn (Karyawan $employee): string => (string) $employee->id)->all();
        sort($ids, SORT_STRING);

        return $ids;
    }

    public function period(Carbon $date): ?object
    {
        $key = $date->format('Y-m');
        if (! array_key_exists($key, $this->periods)) {
            $this->periods[$key] = Schema::hasTable('payroll_attendance_periods') ? DB::table('payroll_attendance_periods')->where('year', $date->year)->where('month', $date->month)->first() : null;
        }

        return $this->periods[$key];
    }

    public function payload(?object $period): ?array
    {
        return $period?->snapshot ? json_decode($period->snapshot, true, 512, JSON_THROW_ON_ERROR) : null;
    }

    public function dates(Carbon $period): array
    {
        return ['from' => $period->copy()->startOfMonth()->subMonth()->day(26)->toDateString(), 'to' => $period->copy()->startOfMonth()->day(25)->toDateString()];
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['attendance' => $message]);
    }

    private function request(array $data): array
    {
        if (! $this->enabled() || ! app(AttendanceConnector::class)->configured()) {
            $this->fail('Integrasi absensi belum siap. Penggajian diblokir.');
        }
        $client = Http::acceptJson()->withToken(config('attendance.token'))->withoutRedirecting()->connectTimeout(3)->timeout(30);
        if (config('attendance.ca_bundle')) {
            $client = $client->withOptions(['verify' => config('attendance.ca_bundle')]);
        }
        try {
            $response = $client->post(rtrim(config('attendance.url'), '/').'/api/v1/integration/payroll-snapshot', $data);
        } catch (ConnectionException) {
            $this->fail('Koneksi absensi gagal. Gaji belum diproses. Coba kembali; transaksi penguncian yang sama akan digunakan.');
        }
        if (! $response->successful()) {
            if (! empty($data['request_id']) && $response->clientError()) {
                DB::table('payroll_attendance_periods')->where('request_id', $data['request_id'])->whereNull('remote_locked_at')->update(['lock_attempted_at' => null]);
                $this->clear();
            }
            $this->fail($response->status() === 409 ? 'Data absensi berubah atau periode terkunci. Perbarui data dan tinjau ulang sebelum memproses gaji.' : 'Data absensi belum dapat diperiksa. Penggajian diblokir; periksa integrasi dan persetujuan HR.');
        }
        $snapshot = $response->json('data');
        if (! is_array($snapshot) || ($snapshot['schema_version'] ?? null) !== 1 || ($snapshot['monitoring_version'] ?? null) !== 1 || ($snapshot['fuel_allowance_version'] ?? null) !== 1 || ! is_bool($snapshot['ready'] ?? null) || ! is_array($snapshot['employees'] ?? null) || ! is_array($snapshot['issues'] ?? null) || ! preg_match('/^[a-f0-9]{64}$/', $snapshot['revision'] ?? '') || ($snapshot['from'] ?? null) !== $data['from'] || ($snapshot['to'] ?? null) !== $data['to']) {
            $this->fail('Format snapshot absensi belum mendukung perhitungan gaji/BBM yang lengkap. Perbarui backend absensi sebelum memproses gaji.');
        }
        $expected = $data['source_employee_ids'];
        sort($expected, SORT_STRING);
        $received = $snapshot['source_employee_ids'] ?? [];
        if (array_filter($snapshot['employees'], fn ($person): bool => ! is_array($person)) !== []) {
            $this->fail('Rincian pegawai snapshot tidak valid.');
        }
        $actual = array_map(fn ($person): string => (string) ($person['source_employee_id'] ?? ''), $snapshot['employees']);
        if (! is_array($received)) {
            $this->fail('Daftar pegawai snapshot tidak valid.');
        }
        sort($received, SORT_STRING);
        sort($actual, SORT_STRING);
        if ($expected !== $received || $expected !== $actual) {
            $this->fail('Daftar pegawai snapshot tidak lengkap atau tidak sesuai perusahaan ini.');
        }
        if (isset($data['request_id']) && (($snapshot['lock_request_id'] ?? null) !== $data['request_id'] || ! hash_equals($data['revision'], $snapshot['revision']))) {
            $this->fail('Konfirmasi penguncian absensi tidak sesuai. Gaji tidak diproses.');
        }
        $dates = [];
        for ($date = Carbon::parse($data['from']); $date->toDateString() <= $data['to']; $date->addDay()) {
            $dates[] = $date->toDateString();
        }
        foreach ($snapshot['employees'] as $person) {
            if (! is_array($person['days'] ?? null) || array_column($person['days'], 'date') !== $dates || ! is_bool($person['attendance_required'] ?? null)) {
                $this->fail('Rincian hari absensi tidak lengkap.');
            }
            foreach ($person['days'] as $day) {
                if (! in_array($day['status'] ?? '', ['present', 'half_day', 'absent', 'excused', 'exempt', 'before_tracking', 'off', 'holiday', 'pending', 'invalid', 'upcoming', 'in_progress', 'pending_leave'], true)) {
                    $this->fail('Status absensi tidak dikenal.');
                }
                foreach (['scheduled_minutes', 'break_minutes', 'late_minutes', 'early_minutes', 'check_in_late_minutes'] as $field) {
                    if (! is_int($day[$field] ?? null) || $day[$field] < 0 || $day[$field] > 10080) {
                        $this->fail('Rincian menit absensi tidak valid.');
                    }
                }
                if (! array_key_exists('check_in_at', $day) || ($day['check_in_at'] !== null && (! is_string($day['check_in_at']) || ! strtotime($day['check_in_at'])))) {
                    $this->fail('Waktu absen masuk tidak valid.');
                }
                if (in_array($day['status'], ['present', 'half_day'], true) && $day['check_in_at'] === null) {
                    $this->fail('Catatan hadir tidak memiliki waktu absen masuk.');
                }
                if ($snapshot['ready'] && (in_array($day['status'], ['pending', 'invalid', 'upcoming', 'in_progress', 'pending_leave'], true) || (in_array($day['status'], ['present', 'half_day', 'absent'], true) && $day['scheduled_minutes'] <= $day['break_minutes']))) {
                    $this->fail('Periode atau jadwal absensi belum siap untuk penggajian.');
                }
            }
        }
        if ($snapshot['ready'] && $snapshot['issues'] !== []) {
            $this->fail('Masih ada masalah absensi yang harus diselesaikan HR.');
        }

        return $snapshot;
    }

    public function refresh(Carbon $date, array $sources): void
    {
        if (! Schema::hasTable('payroll_attendance_periods')) {
            $this->fail('Migrasi integrasi penggajian belum dijalankan.');
        }
        $this->clear();
        $period = $this->period($date);
        if ($period?->sealed_at || $period?->lock_attempted_at || $period?->remote_locked_at || RekapGaji::where('tahun', $date->year)->where('bulan', $date->month)->exists()) {
            $this->fail('Periode sudah dikunci/diproses. Lanjutkan transaksi yang sama; data tidak dapat diganti.');
        }
        sort($sources, SORT_STRING);
        $snapshot = $this->request($this->dates($date) + ['source_employee_ids' => $sources]);
        DB::transaction(function () use ($date, $snapshot): void {
            $dates = $this->dates($date);
            DB::table('payroll_attendance_periods')->insertOrIgnore(['year' => $date->year, 'month' => $date->month, 'from_date' => $dates['from'], 'to_date' => $dates['to'], 'request_id' => (string) Str::uuid(), 'created_at' => now(), 'updated_at' => now()]);
            $row = DB::table('payroll_attendance_periods')->where('year', $date->year)->where('month', $date->month)->lockForUpdate()->first();
            if ($row->lock_attempted_at || $row->remote_locked_at || $row->sealed_at) {
                $this->fail('Periode sedang atau sudah dikunci. Muat ulang form.');
            }
            DB::table('payroll_attendance_periods')->where('id', $row->id)->update(['snapshot' => json_encode($snapshot, JSON_THROW_ON_ERROR), 'fetched_at' => now(), 'updated_at' => now()]);
        });
        $this->clear();
    }

    public function assertReady(Carbon $date, array $sources, ?string $revision): object
    {
        $period = $this->period($date);
        $payload = $this->payload($period);
        sort($sources, SORT_STRING);
        if (! $period || $period->sealed_at || ! ($payload['ready'] ?? false) || ($payload['source_employee_ids'] ?? []) !== $sources || ! $revision || ! hash_equals($payload['revision'], $revision)) {
            $this->fail('Perbarui data absensi dan selesaikan masalah/perubahan pegawai sebelum memproses gaji.');
        }

        return $period;
    }

    public function lock(Carbon $date, array $sources, ?string $revision): object
    {
        $this->clear();
        $period = $this->assertReady($date, $sources, $revision);
        DB::transaction(function () use ($period, $revision): void {
            $current = DB::table('payroll_attendance_periods')->where('id', $period->id)->lockForUpdate()->first();
            if ($current->sealed_at || ! hash_equals($this->payload($current)['revision'], $revision)) {
                $this->fail('Data absensi berubah saat konfirmasi. Muat ulang form.');
            }
            DB::table('payroll_attendance_periods')->where('id', $current->id)->update(['lock_attempted_at' => $current->lock_attempted_at ?? now()]);
        });
        $this->request(['from' => $period->from_date, 'to' => $period->to_date, 'source_employee_ids' => $sources, 'revision' => $revision, 'request_id' => $period->request_id]);
        DB::table('payroll_attendance_periods')->where('id', $period->id)->update(['remote_locked_at' => now(), 'updated_at' => now()]);
        $this->clear();

        return $this->period($date);
    }

    public function seal(?object $period, int $rekap): void
    {
        if ($period) {
            DB::table('payroll_attendance_periods')->where('id', $period->id)->update(['sealed_at' => now(), 'rekap_gaji_id' => $rekap, 'updated_at' => now()]);
            $this->clear();
        }
    }

    public function calculate(Karyawan $employee, Carbon $date): array
    {
        $empty = ['potongan_absensi' => 0, 'tunjangan_bbm' => 0, 'rincian_absensi' => null, 'attendance_revision' => null];
        if (! $this->enabled()) {
            return $empty;
        }
        $period = $this->period($date);
        $snapshot = $this->payload($period);
        if (! $snapshot) {
            return $empty;
        }
        $this->people[$period->id] ??= array_column($snapshot['employees'], null, 'source_employee_id');
        $person = $this->people[$period->id][(string) $employee->id] ?? null;
        if (! $person) {
            return $empty;
        }
        $sales = $employee->jabatan?->isSales() ?? false;
        $base = (int) $employee->gaji_pokok + (int) $employee->tunjangan_jabatan + (int) $employee->tunjangan_keluarga;
        $daily = $sales ? max(0, (int) $employee->tunjangan_bbm) : (int) round($base / 25);
        $cap = $sales ? null : intdiv($base, 4);
        $raw = 0;
        $gross = 0;
        $reduction = 0;
        $days = [];
        foreach ($person['days'] as $day) {
            $effective = max(0, $day['scheduled_minutes'] - $day['break_minutes']);
            $amount = 0;
            $entitled = 0;
            if ($sales) {
                if ($day['check_in_at'] !== null && $day['scheduled_minutes'] > 0 && in_array($day['status'], ['present', 'half_day', 'in_progress'], true)) {
                    $entitled = $daily;
                    $amount = $effective > 0 ? (int) round(min($effective, $day['check_in_late_minutes']) / $effective * $daily) : $daily;
                }
                $gross += $entitled;
                $reduction += $amount;
                $raw += $entitled - $amount;
            } elseif ($person['attendance_required']) {
                $amount = match ($day['status']) {
                    'absent' => $daily,
                    'half_day' => (int) round($daily / 2),
                    'present' => $effective > 0 ? (int) round(min($effective, $day['late_minutes'] + $day['early_minutes']) / $effective * $daily) : 0,
                    default => 0,
                };
                $raw += $amount;
            }
            $days[] = $day + ['effective_minutes' => $effective, 'daily_rate' => $daily, 'deduction' => $amount, 'bbm_gross' => $entitled, 'bbm_received' => $sales ? $entitled - $amount : 0];
        }
        $detail = ['kind' => $sales ? 'bbm' : 'deduction', 'from' => $snapshot['from'], 'to' => $snapshot['to'], 'timezone' => $snapshot['timezone'] ?? 'Asia/Jakarta', 'as_of' => $snapshot['as_of'] ?? $period->fetched_at, 'revision' => $snapshot['revision'], 'daily_rate' => $daily, 'base' => $base, 'divisor' => $sales ? null : 25, 'cap' => $cap, 'raw' => $raw, 'amount' => $sales ? $raw : min($cap, $raw), 'bbm_gross' => $gross, 'bbm_reduction' => $reduction, 'provisional' => ! $snapshot['ready'], 'days' => $days];

        return ['potongan_absensi' => $sales ? 0 : $detail['amount'], 'tunjangan_bbm' => $sales ? $raw : 0, 'rincian_absensi' => $detail, 'attendance_revision' => $snapshot['revision']];
    }
}
