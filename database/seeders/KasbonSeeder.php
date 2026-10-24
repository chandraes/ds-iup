<?php

namespace Database\Seeders;

use Database\Factories\KasbonFactory;
use Illuminate\Database\Seeder;

class KasbonSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('testing')) {
            throw new \LogicException('Seeder kasbon hanya untuk database testing, agar tidak membuat transaksi kas fiktif.');
        }
        KasbonFactory::new()->create();
        KasbonFactory::new()->installment()->create();
    }
}
