<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\GroupWa;

class GroupWaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['untuk' => 'kas-besar-ppn', 'nama_group' => 'Testing Group'],
            ['untuk' => 'kas-besar-non-ppn', 'nama_group' => 'Testing Group'],
            ['untuk' => 'kas-kecil', 'nama_group' => 'Testing Group'],
        ];

        foreach ($data as $d) {
            GroupWa::create($d);
        }
    }
}
