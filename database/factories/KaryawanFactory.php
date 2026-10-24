<?php

namespace Database\Factories;

use App\Models\db\Karyawan;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Karyawan> */
class KaryawanFactory extends Factory
{
    protected $model = Karyawan::class;

    public function definition(): array
    {
        return ['jabatan_id' => JabatanFactory::new(), 'nomor' => fake()->unique()->numberBetween(1, 100000),
            'nama' => fake()->name(), 'nickname' => fake()->firstName(), 'status' => 1,
            'gaji_pokok' => 3000000, 'tunjangan_jabatan' => 500000, 'tunjangan_keluarga' => 500000,
            'nik' => fake()->numerify('################'), 'npwp' => '-', 'bpjs_tk' => '-', 'bpjs_kesehatan' => '-',
            'apa_bpjs_tk' => 0, 'apa_bpjs_kes' => 0, 'tempat_lahir' => 'Palembang', 'tanggal_lahir' => '1990-01-01',
            'alamat' => fake()->address(), 'no_hp' => '081234567890', 'bank' => 'Bank uji', 'no_rek' => '0012345678',
            'nama_rek' => 'Pegawai uji', 'mulai_bekerja' => '2020-01-01'];
    }
}
