<?php

namespace Database\Factories;

use App\Models\db\Konsumen;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Konsumen> */
class KonsumenFactory extends Factory
{
    protected $model = Konsumen::class;

    public function definition(): array
    {
        return ['kode' => fake()->unique()->numberBetween(1, 100000), 'nama' => fake()->company(), 'cp' => fake()->name(), 'no_hp' => '081234567890', 'npwp' => '-', 'alamat' => fake()->address(), 'pembayaran' => 1, 'plafon' => 0, 'tempo_hari' => 0];
    }
}
