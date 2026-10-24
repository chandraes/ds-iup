<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RekapLandingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['database.default' => 'rekap_landing_test', 'database.connections.rekap_landing_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        $user = new User(['name' => 'Admin Uji', 'role' => 'admin']);
        $user->id = 999;
        $this->actingAs($user);
    }

    public function test_landing_loads_without_querying_customers(): void
    {
        DB::enableQueryLog();

        $this->get(route('rekap'))->assertOk()->assertSee('konsumen-options');

        $this->assertSame([], DB::getQueryLog());
    }

    public function test_customer_search_is_bounded_paginated_and_excludes_inactive_customers(): void
    {
        Schema::create('kode_tokos', function (Blueprint $table): void {
            $table->id();
            $table->string('kode');
        });
        DB::table('kode_tokos')->insert(['id' => 1, 'kode' => 'ABC']);
        Schema::create('konsumens', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('kode_toko_id')->nullable();
            $table->string('nama');
            $table->boolean('active');
        });
        for ($index = 1; $index <= 22; $index++) {
            DB::table('konsumens')->insert(['nama' => sprintf('Toko %02d', $index), 'active' => true, 'kode_toko_id' => 1]);
        }
        DB::table('konsumens')->insert(['nama' => 'Toko Nonaktif', 'active' => false]);
        $url = route('rekap.konsumen-options');

        $this->getJson($url.'?search=Toko')->assertOk()->assertJsonCount(20, 'results')->assertJsonPath('pagination.more', true)
            ->assertJsonPath('results.0.text', 'ABC Toko 01');
        $this->getJson($url.'?search=Toko&page=2')->assertOk()->assertJsonCount(2, 'results')->assertJsonPath('pagination.more', false)
            ->assertJsonPath('results.0.text', 'ABC Toko 21');
        $this->getJson($url.'?search=Nonaktif')->assertOk()->assertJsonCount(0, 'results');
        $this->getJson($url)->assertOk()->assertJsonCount(20, 'results')->assertJsonPath('pagination.more', true);
        $this->getJson($url.'?search=T')->assertOk()->assertJsonCount(20, 'results');
        $this->getJson($url.'?search=ABC')->assertOk()->assertJsonCount(20, 'results');
        DB::table('konsumens')->insert(['nama' => 'Tanpa Kode', 'active' => true]);
        $this->getJson($url.'?search=Tanpa')->assertOk()->assertJsonPath('results.0.text', 'Tanpa Kode');
        $this->getJson($url.'?page=0')->assertUnprocessable();
        $this->getJson($url.'?search='.str_repeat('a', 101))->assertUnprocessable();
        $this->app['auth']->forgetGuards();
        $this->getJson($url.'?search=Toko')->assertUnauthorized();
    }
}
