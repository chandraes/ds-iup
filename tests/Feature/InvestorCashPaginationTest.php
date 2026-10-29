<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InvestorCashPaginationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'investor_pagination_test', 'database.connections.investor_pagination_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        Schema::create('investor_modals', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
        });
        Schema::create('kas_besars', function (Blueprint $table): void {
            $table->id();
            $table->integer('investor_modal_id');
            $table->integer('modal_investor')->nullable();
            $table->integer('nominal');
            $table->boolean('jenis');
            $table->boolean('ppn_kas')->default(true);
            $table->string('uraian')->nullable();
            $table->timestamps();
        });
        DB::table('investor_modals')->insert([['id' => 1, 'nama' => 'Investor A'], ['id' => 2, 'nama' => 'Investor B']]);
        for ($id = 1; $id <= 25; $id++) {
            DB::table('kas_besars')->insert(['id' => $id, 'investor_modal_id' => 1, 'modal_investor' => 100,
                'nominal' => $id * 100, 'jenis' => $id % 2, 'created_at' => '2026-10-01 08:00:00', 'updated_at' => '2026-10-01 08:00:00']);
        }
        DB::table('kas_besars')->insert(['investor_modal_id' => 2, 'modal_investor' => 100, 'nominal' => 999999, 'jenis' => 1]);
        DB::table('kas_besars')->insert(['investor_modal_id' => 1, 'modal_investor' => null, 'nominal' => 888888, 'jenis' => 1]);
        $this->actingAs(new User(['name' => 'Admin', 'role' => 'admin']));
    }

    public function test_datatables_start_returns_different_pages_and_correct_signed_amounts(): void
    {
        $first = $this->getJson('/rekap/kas-investor/detail/1?draw=1&start=0&length=10', ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertJsonPath('recordsTotal', 25)->assertJsonCount(10, 'data');
        $second = $this->getJson('/rekap/kas-investor/detail/1?draw=2&start=10&length=10', ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertJsonPath('draw', 2)->assertJsonPath('recordsFiltered', 25)->assertJsonCount(10, 'data');
        $this->assertSame([], array_values(array_intersect(array_column($first->json('data'), 'id'), array_column($second->json('data'), 'id'))));
        $this->assertSame(range(25, 16), array_column($first->json('data'), 'id'));
        $this->assertSame(range(15, 6), array_column($second->json('data'), 'id'));
        $this->assertSame(2500, (int) $first->json('data.0.nominal'));
        $this->assertSame(-2400, (int) $first->json('data.1.nominal'));
        $this->assertSame('Deposit', $first->json('data.0.uraian'));
        $this->assertSame(array_sum(array_column($second->json('data'), 'nominal')), $second->json('total'));
        $this->getJson('/rekap/kas-investor/detail/1?start=20&length=10', ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->assertJsonCount(5, 'data');
    }

    public function test_requested_sort_is_used_and_invalid_pagination_is_rejected(): void
    {
        $this->getJson('/rekap/kas-investor/detail/1?start=0&length=10&order[0][column]=3&order[0][dir]=asc', ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertJsonPath('data.0.id', 1);
        $this->getJson('/rekap/kas-investor/detail/1?start=-1&length=100000', ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertUnprocessable()->assertJsonValidationErrors(['start', 'length']);
    }
}
