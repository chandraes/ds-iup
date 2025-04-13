<?php

namespace Database\Seeders;

use App\Models\Rekening;
use Illuminate\Database\Seeder;

class RekeningSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'untuk' => 'kas-besar-ppn',
                'bank' => 'BCA',
                'no_rek' => '1234567890',
                'nama_rek' => 'PT. ABC',
            ],
            [
                'untuk' => 'kas-besar-non-ppn',
                'bank' => 'BCA',
                'no_rek' => '1234567890',
                'nama_rek' => 'PT. ABC',
            ],
            [
                'untuk' => 'kas-kecil',
                'bank' => 'BCA',
                'no_rek' => '1234567890',
                'nama_rek' => 'PT. ABC',
            ],

        ];

        foreach ($data as $key => $value) {
            Rekening::create($value);
        }

    }
}
