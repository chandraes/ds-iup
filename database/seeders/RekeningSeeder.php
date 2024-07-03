<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Rekening;

class RekeningSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'untuk' => 'kas-besar',
                'bank' => 'BCA',
                'no_rek' => '1234567890',
                'nama_rek' => 'PT. ABC'
            ],
            [
                'untuk' => 'kas-kecil',
                'bank' => 'BCA',
                'no_rek' => '1234567890',
                'nama_rek' => 'PT. ABC'
            ],

        ];

        foreach ($data as $key => $value) {
            Rekening::create($value);
        }

    }
}
