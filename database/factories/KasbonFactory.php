<?php

namespace Database\Factories;

use App\Models\KasBesar;
use App\Models\Kasbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Kasbon> */
class KasbonFactory extends Factory
{
    protected $model = Kasbon::class;

    public function definition(): array
    {
        return ['karyawan_id' => KaryawanFactory::new(), 'jenis' => 'potong_gaji', 'tanggal' => today(),
            'nominal' => 100000, 'sisa_kas' => 100000, 'total_bayar' => 0, 'cicil_kali' => 1, 'cicilan_nominal' => 0,
            'mulai_periode' => today()->startOfMonth(), 'lunas' => false, 'void' => false, 'submission_token' => (string) Str::uuid(),
            'kas_besar_id' => fn (): int => KasBesar::create(['ppn_kas' => 1, 'jenis' => 0, 'uraian' => 'Pencairan kasbon uji',
                'nominal' => 100000, 'saldo' => 10000000, 'modal_investor_terakhir' => 0, 'bank' => '-', 'nama_rek' => '-', 'no_rek' => '-'])->id];
    }

    public function installment(): static
    {
        return $this->state(fn (): array => ['jenis' => 'cicilan', 'nominal' => 900000, 'sisa_kas' => 900000, 'cicil_kali' => 3, 'cicilan_nominal' => 300000]);
    }
}
