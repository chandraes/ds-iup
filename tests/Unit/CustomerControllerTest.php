<?php

namespace Tests\Unit;

use Tests\TestCase;

class CustomerControllerTest extends TestCase
{
    public function test_db_konsumen_store_requires_authentication(): void
    {
        $response = $this->post('/db/konsumen/store', [
            'kode_toko_id' => 1,
            'nik' => '1234567890',
            'nama' => 'Test',
            'cp' => 'Test CP',
            'no_hp' => '1234567890',
            'npwp' => '123456789',
            'provinsi_id' => 1,
            'kabupaten_kota_id' => 1,
            'alamat' => 'Test Address',
            'pembayaran' => 1,
            'plafon' => '1000',
            'tempo_hari' => 30,
            'karyawan_id' => 1,
        ]);

        $response->assertRedirect('/login');
    }
}
