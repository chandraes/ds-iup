<?php

namespace Database\Factories;

use App\Models\db\Jabatan;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Jabatan> */
class JabatanFactory extends Factory
{
    protected $model = Jabatan::class;

    public function definition(): array
    {
        return ['nama' => 'Administrasi'];
    }
}
