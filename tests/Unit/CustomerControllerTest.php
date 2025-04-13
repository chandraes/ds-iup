<?php

namespace Tests\Unit;

use Illuminate\Http\Request;
// use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CustomerControllerTest extends TestCase
{
    // use RefreshDatabase;

    public function test_store()
    {
        $data = [
            'nama' => 'Test',
            'singkatan' => 'T',
            'cp' => 'Test CP',
            'no_wa' => '1234567890',
            'alamat' => 'Test Address',
            'harga' => '1000',
        ];

        // Validate the data
        $validator = Validator::make($data, [
            'nama' => 'required',
            'singkatan' => 'required',
            'cp' => 'required',
            'no_wa' => 'required',
            'alamat' => 'required',
            'harga' => 'required',
        ]);

        if ($validator->fails()) {
            $this->fail('Request validation failed');
        }

        $request = new Request($data);

        $controller = new \App\Http\Controllers\CustomerController;
        $controller->store($request);

        $this->assertDatabaseHas('customers', $data);
    }
}
