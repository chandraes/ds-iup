<?php

namespace App\Services;

use App\Models\db\Karyawan;
use App\Models\KasBesar;
use App\Models\RekapGaji;
use App\Models\RekapGajiDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmployeePayrollService
{
    public function __construct(private KasbonService $kasbon) {}

    /** @return array<string, int> */
    public function amounts(Karyawan $employee, Carbon $period, bool $lock = false): array
    {
        $base = (int) $employee->gaji_pokok;
        $tk = $employee->apa_bpjs_tk ? (int) round($base * 0.049) : 0;
        $kes = $employee->apa_bpjs_kes ? (int) round($base * 0.04) : 0;
        $potTk = $employee->apa_bpjs_tk ? (int) round($base * 0.02) : 0;
        $potKes = $employee->apa_bpjs_kes ? (int) round($base * 0.01) : 0;
        $income = $base + (int) $employee->tunjangan_jabatan + (int) $employee->tunjangan_keluarga;
        $net = $income - $potTk - $potKes;
        $deduction = array_sum(array_column($this->kasbon->payments($employee, $period, $lock), 'amount'));

        return ['bpjs_tk' => $tk, 'bpjs_k' => $kes, 'potongan_bpjs_tk' => $potTk, 'potongan_bpjs_kesehatan' => $potKes,
            'pendapatan_kotor' => $income + $tk + $kes, 'pendapatan_bersih' => $net,
            'potongan_kasbon' => $deduction, 'sisa_gaji_dibayar' => $net - $deduction];
    }

    public function store(): RekapGaji
    {
        return DB::transaction(function (): RekapGaji {
            $period = now()->startOfMonth();
            $employees = Karyawan::with('jabatan')->where('status', 1)->orderBy('id')->lockForUpdate()->get();
            if ($employees->isEmpty()) {
                throw ValidationException::withMessages(['total' => 'Tidak ada pegawai aktif.']);
            }
            if (RekapGaji::where('bulan', $period->month)->where('tahun', $period->year)->lockForUpdate()->exists()) {
                throw ValidationException::withMessages(['total' => 'Gaji bulan ini sudah dibuat.']);
            }
            $cash = KasBesar::where('ppn_kas', 1)->orderByDesc('id')->lockForUpdate()->first();
            $rekap = RekapGaji::create(['bulan' => $period->month, 'tahun' => $period->year, 'uraian' => 'Gaji Bulan '.$period->format('F Y'), 'total' => 0]);
            $total = 0;
            foreach ($employees as $employee) {
                $amounts = $this->amounts($employee, $period, true);
                if ($amounts['sisa_gaji_dibayar'] < 0) {
                    throw ValidationException::withMessages(['total' => 'Potongan melebihi gaji bersih '.$employee->nama.'.']);
                }
                RekapGajiDetail::create(array_merge($amounts, ['rekap_gaji_id' => $rekap->id,
                    'nik' => ($employee->kode ?? '').sprintf('%03d', $employee->nomor), 'nama' => $employee->nama,
                    'jabatan' => $employee->jabatan?->nama ?? '—', 'gaji_pokok' => $employee->gaji_pokok,
                    'tunjangan_jabatan' => $employee->tunjangan_jabatan, 'tunjangan_keluarga' => $employee->tunjangan_keluarga,
                    'bank' => $employee->bank, 'no_rek' => $employee->no_rek, 'nama_rek' => $employee->nama_rek]));
                foreach ($this->kasbon->payments($employee, $period, true) as $payment) {
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

            return $rekap;
        }, 3);
    }
}
