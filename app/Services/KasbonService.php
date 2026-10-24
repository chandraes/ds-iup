<?php

namespace App\Services;

use App\Models\db\Karyawan;
use App\Models\KasBesar;
use App\Models\Kasbon;
use App\Models\RekapGaji;
use App\Models\Rekening;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class KasbonService
{
    public function ensureEligible(Karyawan $employee): void
    {
        if ((int) $employee->status !== 1) {
            throw ValidationException::withMessages(['karyawan_id' => 'Kasbon hanya tersedia untuk pegawai aktif.']);
        }
        if ($employee->jabatan?->isProtectedSales()) {
            throw ValidationException::withMessages(['karyawan_id' => 'Kasbon Sales belum tersedia sampai aturan khusus ditetapkan.']);
        }
    }

    /** @return array{income: int, ordinary_due: int, installment_due: int, ordinary_limit: int, ordinary_available: int, installment_limit: int} */
    public function summary(Karyawan $employee, Carbon $period, bool $lock = false): array
    {
        $query = Kasbon::where('karyawan_id', $employee->id)->where('void', false)->where('lunas', false)->orderBy('id');
        if ($lock) {
            $query->lockForUpdate();
        }
        $ordinary = 0;
        $installments = 0;
        foreach ($query->get() as $loan) {
            if ($loan->tanggal->gt($period->copy()->endOfMonth())) {
                continue;
            }
            if ($loan->jenis === 'potong_gaji') {
                $ordinary += (int) $loan->sisa_kas;
            } elseif ($loan->mulai_periode->lte($period->copy()->startOfMonth())) {
                $installments += $this->monthlyPayment($loan);
            }
        }
        $income = (int) $employee->gaji_pokok + (int) $employee->tunjangan_jabatan + (int) $employee->tunjangan_keluarga;
        $ordinaryLimit = intdiv(max(0, $income - $installments) * 50, 100);

        return ['income' => $income, 'ordinary_due' => $ordinary, 'installment_due' => $installments,
            'ordinary_limit' => $ordinaryLimit, 'ordinary_available' => max(0, $ordinaryLimit - $ordinary),
            'installment_limit' => intdiv(max(0, $income - $ordinary) * 30, 100)];
    }

    public function monthlyPayment(Kasbon $loan): int
    {
        $paidMonths = intdiv((int) $loan->total_bayar, max(1, (int) $loan->cicilan_nominal));

        return $paidMonths >= $loan->cicil_kali - 1 ? (int) $loan->sisa_kas : min((int) $loan->sisa_kas, (int) $loan->cicilan_nominal);
    }

    /** @param array{karyawan_id: int|string, jenis: string, nominal: int, cicil_kali?: int|string, mulai_bulan?: int|string, mulai_tahun?: int|string, submission_token: string} $data */
    public function create(array $data): Kasbon
    {
        return DB::transaction(function () use ($data): Kasbon {
            $employee = Karyawan::whereKey($data['karyawan_id'])->lockForUpdate()->firstOrFail();
            $this->ensureEligible($employee);
            $existing = Kasbon::where('submission_token', $data['submission_token'])->lockForUpdate()->first();
            if ($existing) {
                if ((int) $existing->karyawan_id !== $employee->id || $existing->jenis !== $data['jenis'] || (int) $existing->nominal !== $data['nominal']) {
                    throw ValidationException::withMessages(['nominal' => 'Form sudah digunakan. Muat ulang sebelum membuat pengajuan baru.']);
                }

                return $existing;
            }
            $installment = $data['jenis'] === 'cicilan';
            if (! $installment && now()->day < 16) {
                throw ValidationException::withMessages(['nominal' => 'Kasbon potong gaji tersedia mulai tanggal 16 setiap bulan.']);
            }
            $period = $installment ? Carbon::create((int) $data['mulai_tahun'], (int) $data['mulai_bulan'], 1)->startOfDay() : now()->startOfMonth();
            if ($period->lt(now()->startOfMonth())) {
                throw ValidationException::withMessages(['mulai_bulan' => 'Periode mulai cicilan tidak boleh sebelum bulan berjalan.']);
            }
            if (RekapGaji::where('bulan', $period->month)->where('tahun', $period->year)->lockForUpdate()->exists()) {
                throw ValidationException::withMessages(['nominal' => 'Gaji periode ini sudah diproses. Pilih periode berikutnya untuk cicilan.']);
            }
            if ($installment && Kasbon::where('karyawan_id', $employee->id)->where('jenis', 'cicilan')->where('lunas', false)->where('void', false)->lockForUpdate()->exists()) {
                throw ValidationException::withMessages(['karyawan_id' => 'Pegawai masih memiliki kasbon cicilan yang belum lunas.']);
            }
            $summary = $this->summary($employee, $period, true);
            $count = $installment ? (int) $data['cicil_kali'] : 1;
            $monthly = (int) ceil($data['nominal'] / $count);
            if ((! $installment && $data['nominal'] > $summary['ordinary_available']) || ($installment && $monthly > $summary['installment_limit'])) {
                throw ValidationException::withMessages(['nominal' => 'Nominal melebihi batas kasbon berdasarkan penghasilan dan pinjaman berjalan.']);
            }
            if ($installment && $count > $data['nominal']) {
                throw ValidationException::withMessages(['cicil_kali' => 'Jumlah cicilan tidak boleh melebihi nominal rupiah pinjaman.']);
            }
            $cash = $this->cash($employee, $data['nominal'], false, 'Kasbon '.($installment ? 'cicilan ' : 'potong gaji ').$employee->nama);

            return Kasbon::create(['karyawan_id' => $employee->id, 'jenis' => $data['jenis'], 'tanggal' => today(),
                'nominal' => $data['nominal'], 'sisa_kas' => $data['nominal'], 'cicil_kali' => $count,
                'cicilan_nominal' => $installment ? intdiv($data['nominal'], $count) : 0,
                'mulai_periode' => $period, 'kas_besar_id' => $cash->id, 'submission_token' => $data['submission_token']]);
        }, 3);
    }

    public function cancel(Kasbon $loan, string $reason): void
    {
        DB::transaction(function () use ($loan, $reason): void {
            $employee = Karyawan::whereKey($loan->karyawan_id)->lockForUpdate()->firstOrFail();
            $loan = Kasbon::whereKey($loan->id)->lockForUpdate()->firstOrFail();
            if ($loan->void || $loan->lunas || $loan->sisa_kas < 1) {
                throw ValidationException::withMessages(['alasan' => 'Kasbon sudah lunas atau dibatalkan.']);
            }
            $cash = $this->cash($employee, (int) $loan->sisa_kas, true, 'Pengembalian kasbon #'.$loan->id.' '.$employee->nama);
            $loan->update(['void' => true, 'alasan_void' => $reason, 'void_kas_besar_id' => $cash->id, 'sisa_kas' => 0]);
        }, 3);
    }

    private function cash(Karyawan $employee, int $amount, bool $incoming, string $description): KasBesar
    {
        $last = KasBesar::where('ppn_kas', 1)->orderByDesc('id')->lockForUpdate()->first();
        if (! $last || (! $incoming && $last->saldo < $amount)) {
            throw ValidationException::withMessages(['nominal' => 'Saldo Kas Besar PPN tidak mencukupi.']);
        }

        $account = $incoming ? Rekening::where('untuk', 'kas-besar-ppn')->first() : $employee;
        if (! $account) {
            throw ValidationException::withMessages(['alasan' => 'Rekening Kas Besar PPN belum diatur.']);
        }

        return KasBesar::create(['ppn_kas' => 1, 'jenis' => $incoming ? 1 : 0, 'uraian' => $description,
            'nominal' => $amount, 'saldo' => $last->saldo + ($incoming ? $amount : -$amount),
            'modal_investor_terakhir' => $last->modal_investor_terakhir, 'nama_rek' => $account->nama_rek,
            'no_rek' => $account->no_rek, 'bank' => $account->bank]);
    }

    /** @return list<array{loan: Kasbon, amount: int}> */
    public function payments(Karyawan $employee, Carbon $period, bool $lock = false): array
    {
        $query = Kasbon::where('karyawan_id', $employee->id)->where('lunas', false)->where('void', false)
            ->whereDate('tanggal', '<=', $period->copy()->endOfMonth())->orderBy('id');
        if ($lock) {
            $query->lockForUpdate();
        }
        $payments = [];
        foreach ($query->get() as $loan) {
            if ($loan->jenis === 'potong_gaji' || $loan->mulai_periode->lte($period->copy()->startOfMonth())) {
                $amount = $loan->jenis === 'potong_gaji' ? (int) $loan->sisa_kas : $this->monthlyPayment($loan);
                if ($amount > 0) {
                    $payments[] = ['loan' => $loan, 'amount' => $amount];
                }
            }
        }

        return $payments;
    }
}
