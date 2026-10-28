<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class LinkLegacyPayrollEmployees extends Command
{
    protected $signature = 'payroll:link-legacy-employees {--output= : Private CSV report filename} {--apply= : Reviewed CSV filename with approved=yes}';

    protected $description = 'Report legacy payroll ownership candidates; only apply explicitly approved, unchanged mappings';

    public function handle(): int
    {
        try {
            $directory = Storage::disk('local')->path('private/payroll-mapping');
            if (! is_dir($directory) && ! mkdir($directory, 0700, true) && ! is_dir($directory)) {
                throw new RuntimeException('Cannot create private report directory.');
            }
            if ($this->option('apply')) {
                return $this->applyReport($directory, (string) $this->option('apply'));
            }

            return $this->report($directory);
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }

    private function filename(string $value): string
    {
        if (! preg_match('/\A[A-Za-z0-9_-]+\.csv\z/', $value)) {
            throw new RuntimeException('Use a CSV filename without a directory.');
        }

        return $value;
    }

    private function fingerprint(object $row, bool $detail = false): string
    {
        $values = (array) $row;
        if ($detail) {
            unset($values['karyawan_id']);
        }
        ksort($values);

        return hash('sha256', json_encode($values, JSON_THROW_ON_ERROR));
    }

    private function normal(?string $value): string
    {
        return mb_strtolower(trim($value ?? ''));
    }

    private function employeeQuery(): Builder
    {
        return DB::table('karyawans')->select(['id', 'nomor', 'nama', 'bank', 'no_rek', 'nama_rek', 'status']);
    }

    private function report(string $directory): int
    {
        $name = $this->filename($this->option('output') ?: 'legacy-payroll-'.now()->format('Ymd-His-u').'-'.bin2hex(random_bytes(4)).'.csv');
        $path = $directory.'/'.$name;
        $stream = fopen($path, 'x');
        if (! $stream) {
            throw new RuntimeException('Report already exists or cannot be created.');
        }
        chmod($path, 0600);
        $headers = ['detail_id', 'year', 'month', 'legacy_code', 'legacy_name', 'legacy_position', 'legacy_bank', 'legacy_account', 'legacy_account_name', 'candidate_ids', 'candidate_names', 'match_rule', 'employee_id', 'approved', 'detail_fingerprint', 'employee_fingerprint', 'detail_before'];
        fputcsv($stream, $headers, ',', '"', '');
        $employees = $this->employeeQuery()->get()->groupBy(fn ($employee): string => str_pad((string) $employee->nomor, 3, '0', STR_PAD_LEFT));
        $periods = DB::table('rekap_gajis')->get(['id', 'bulan', 'tahun'])->keyBy('id');
        $counts = ['strong' => 0, 'number_only' => 0, 'not_found' => 0, 'ambiguous' => 0];
        try {
            foreach (DB::table('rekap_gaji_details')->whereNull('karyawan_id')->orderBy('id')->cursor() as $detail) {
                $candidates = $employees->get(trim($detail->nik), collect());
                $employee = $candidates->count() === 1 ? $candidates->first() : null;
                $nameMatches = $employee && $this->normal($employee->nama) === $this->normal($detail->nama);
                $accountMatches = $employee && ! in_array(trim($detail->no_rek), ['', '-', '0'], true)
                    && trim($employee->no_rek) === trim($detail->no_rek)
                    && $this->normal($employee->bank) === $this->normal($detail->bank);
                $status = $employee ? ($nameMatches || $accountMatches ? 'strong' : 'number_only') : ($candidates->isEmpty() ? 'not_found' : 'ambiguous');
                $counts[$status]++;
                $period = $periods->get($detail->rekap_gaji_id);
                $values = [$detail->id, $period?->tahun, $period?->bulan, $detail->nik, $detail->nama, $detail->jabatan, $detail->bank, $detail->no_rek, $detail->nama_rek,
                    $candidates->pluck('id')->implode('|'), $candidates->pluck('nama')->implode(' | '), $status,
                    $employee?->id, '', $this->fingerprint($detail, true), $employee ? $this->fingerprint($employee) : '', json_encode($detail, JSON_THROW_ON_ERROR)];
                foreach ([4, 5, 6, 7, 8, 10] as $index) {
                    if (preg_match('/\A[=+@\-\t\r\n]/', (string) $values[$index])) {
                        $values[$index] = "'".$values[$index];
                    }
                }
                fputcsv($stream, $values, ',', '"', '');
            }
        } finally {
            fclose($stream);
        }
        $this->info('Dry run: '.json_encode($counts, JSON_THROW_ON_ERROR));
        $this->info('Private report: '.$path);
        $this->warn('No database rows changed. Review ownership before setting approved=yes. Unresolved rows require identity verification and a refreshed report.');

        return self::SUCCESS;
    }

    private function applyReport(string $directory, string $name): int
    {
        $path = $directory.'/'.$this->filename($name);
        if (! is_file($path) || is_link($path)) {
            throw new RuntimeException('Reviewed private report not found.');
        }
        $stream = fopen($path, 'r');
        $headers = fgetcsv($stream, 0, ',', '"', '');
        $approved = [];
        try {
            while (($values = fgetcsv($stream, 0, ',', '"', '')) !== false) {
                if (count($values) !== count($headers)) {
                    throw new RuntimeException('Malformed report row.');
                }
                $row = array_combine($headers, $values);
                if (($row['approved'] ?? '') !== 'yes') {
                    continue;
                }
                if (! ctype_digit($row['detail_id'] ?? '') || ! ctype_digit($row['employee_id'] ?? '') || isset($approved[$row['detail_id']])) {
                    throw new RuntimeException('Invalid or duplicate approved mapping.');
                }
                $approved[$row['detail_id']] = $row;
            }
        } finally {
            fclose($stream);
        }
        if ($approved === []) {
            throw new RuntimeException('No approved mappings. Nothing changed.');
        }
        ksort($approved, SORT_NUMERIC);
        $auditPath = $directory.'/apply-'.now()->format('Ymd-His-u').'-'.bin2hex(random_bytes(4)).'.json';
        $audit = ['status' => 'planned', 'report' => $name, 'mappings' => array_map(fn ($row): array => ['detail_id' => (int) $row['detail_id'], 'old_karyawan_id' => null, 'new_karyawan_id' => (int) $row['employee_id']], array_values($approved))];
        $this->writeAudit($auditPath, $audit);
        try {
            $changed = DB::transaction(function () use ($approved): int {
                $changed = 0;
                foreach ($approved as $row) {
                    $detail = DB::table('rekap_gaji_details')->where('id', $row['detail_id'])->lockForUpdate()->first();
                    $employee = $this->employeeQuery()->where('id', $row['employee_id'])->lockForUpdate()->first();
                    if (! $detail || ! $employee || ! hash_equals($row['detail_fingerprint'], $this->fingerprint($detail, true)) || ! hash_equals($row['employee_fingerprint'], $this->fingerprint($employee))) {
                        throw new RuntimeException('Mapping is stale or invalid. Regenerate report: detail '.$row['detail_id']);
                    }
                    if ($detail->karyawan_id !== null) {
                        if ((int) $detail->karyawan_id === (int) $employee->id) {
                            continue;
                        }
                        throw new RuntimeException('Payroll detail is already linked to another employee.');
                    }
                    DB::table('rekap_gajis')->where('id', $detail->rekap_gaji_id)->lockForUpdate()->first();
                    if (DB::table('rekap_gaji_details')->where('rekap_gaji_id', $detail->rekap_gaji_id)->where('karyawan_id', $employee->id)->where('id', '!=', $detail->id)->lockForUpdate()->first(['id'])) {
                        throw new RuntimeException('Employee already has a detail in the same payroll period.');
                    }
                    DB::table('rekap_gaji_details')->where('id', $detail->id)->whereNull('karyawan_id')->update(['karyawan_id' => $employee->id]);
                    $changed++;
                }

                return $changed;
            }, 3);
        } catch (Throwable $exception) {
            $audit['status'] = 'rolled_back';
            $this->writeAudit($auditPath, $audit);
            throw $exception;
        }
        $audit['status'] = 'committed';
        $audit['changed'] = $changed;
        try {
            $this->writeAudit($auditPath, $audit);
        } catch (Throwable $exception) {
            Log::critical('Legacy payroll links committed but audit finalization failed', ['audit' => $audit, 'path' => $auditPath, 'error' => $exception->getMessage()]);
            $this->error('DATABASE COMMITTED: '.$changed.' links changed. Audit finalization failed; the private report and application log contain the mapping. Do not assume rollback.');

            return self::FAILURE;
        }
        $this->info('Linked '.$changed.' payroll details. Audit: '.$auditPath);

        return self::SUCCESS;
    }

    /** @param array<string, mixed> $data */
    private function writeAudit(string $path, array $data): void
    {
        if (file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR), LOCK_EX) === false) {
            throw new RuntimeException('Cannot persist mapping audit.');
        }
        chmod($path, 0600);
    }
}
