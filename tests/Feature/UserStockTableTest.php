<?php

namespace Tests\Feature;

use App\Models\db\Barang\BarangStokHarga;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class UserStockTableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['database.default' => 'stock_test', 'database.connections.stock_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        foreach (['barang_units', 'barang_types', 'barang_kategoris', 'barang_namas', 'satuans'] as $name) {
            Schema::create($name, function (Blueprint $table): void {
                $table->id();
                $table->string('nama');
                $table->integer('barang_unit_id')->default(1);
                $table->integer('barang_kategori_id')->default(1);
            });
            DB::table($name)->insert(['id' => 1, 'nama' => 'Contoh']);
            DB::table($name)->insert(['id' => 2, 'nama' => 'Lain']);
        }
        Schema::create('barangs', function (Blueprint $table): void {
            $table->id();
            foreach (['barang_unit_id', 'barang_type_id', 'barang_kategori_id', 'barang_nama_id', 'satuan_id'] as $column) {
                $table->integer($column)->default(1);
            }
            $table->integer('jenis')->default(1);
            $table->boolean('is_active')->default(true);
            $table->string('kode')->default('KODE');
            $table->string('merk')->default('Merk');
        });
        Schema::create('barang_stok_hargas', function (Blueprint $table): void {
            $table->id();
            foreach (['barang_unit_id', 'barang_type_id', 'barang_kategori_id', 'barang_nama_id', 'barang_id'] as $column) {
                $table->integer($column)->default(1);
            }
            $table->integer('stok')->default(5);
            $table->integer('stok_awal')->default(10);
            $table->integer('harga')->default(100000);
            $table->integer('harga_beli')->default(70000);
            $table->integer('min_jual')->nullable();
            $table->boolean('hide')->default(false);
            $table->timestamps();
        });
        Schema::create('harga_submissions', function (Blueprint $table): void {
            $table->id();
            $table->integer('barang_stok_harga_id')->unique();
            $table->integer('harga_ajuan');
            $table->integer('status');
        });
        Schema::create('barang_detail_types', function (Blueprint $table): void {
            $table->id();
            $table->integer('barang_id');
        });
        Schema::create('karyawans', function (Blueprint $table): void {
            $table->id();
            $table->boolean('status');
        });
        Schema::create('pajaks', function (Blueprint $table): void {
            $table->id();
            $table->string('untuk');
            $table->integer('persen');
        });
        DB::table('pajaks')->insert(['untuk' => 'ppn', 'persen' => 11]);
        $this->actingAs(new User(['name' => 'User Uji', 'role' => 'user']));
    }

    private function stock(int $id, array $stock = [], array $product = []): void
    {
        DB::table('barangs')->insert(array_replace(['id' => $id], $product));
        DB::table('barang_stok_hargas')->insert(array_replace(['id' => $id, 'barang_id' => $id, 'created_at' => '2026-10-01 00:00:00'], $stock));
    }

    private function data(array $parameters = []): TestResponse
    {
        return $this->getJson(route('user.stok-all.data', array_replace(['draw' => 1, 'start' => 0, 'length' => 50], $parameters)));
    }

    public function test_scroller_rows_match_legacy_visibility_and_prices_without_internal_attributes(): void
    {
        $this->stock(1);
        $this->stock(2, ['stok' => 0]);
        $this->stock(3, ['stok' => 2], ['is_active' => 0, 'jenis' => 2]);
        $this->stock(4, ['stok' => 0], ['is_active' => 0]);
        $this->stock(5, ['hide' => 1]);
        $this->stock(6, ['stok' => -1]);
        $this->stock(7, ['harga' => 12345], ['jenis' => 3]);
        $this->stock(8, ['harga' => 0]);
        DB::table('harga_submissions')->insert(['barang_stok_harga_id' => 1, 'harga_ajuan' => 120000, 'status' => 1]);
        DB::table('harga_submissions')->insert(['barang_stok_harga_id' => 3, 'harga_ajuan' => 200000, 'status' => 0]);
        $legacy = (new BarangStokHarga)->barangStokAll()->flatten();

        $response = $this->data()->assertOk()->assertJsonPath('recordsTotal', 6)->assertJsonPath('recordsFiltered', 6);

        $this->assertSame($legacy->pluck('id')->all(), array_column($response->json('data'), 'id'));
        foreach ($legacy->values() as $index => $row) {
            $price = $row->harga_temp ? $row->harga_temp->harga_ajuan : $row->harga;
            $expected = number_format($row->barang->jenis == 1 ? $price * 1.11 : $price, 0, ',', '.');
            $this->assertSame($expected, number_format($response->json("data.$index.harga_jual_ppn"), 0, ',', '.'));
            $this->assertSame($row->stok, $response->json("data.$index.stok"));
            $this->assertArrayNotHasKey('harga_beli', $response->json("data.$index"));
            $this->assertArrayNotHasKey('harga_temp', $response->json("data.$index"));
        }
    }

    public function test_filters_and_offset_slices_do_not_duplicate_or_skip_rows_with_tied_dates(): void
    {
        foreach (range(1, 7) as $id) {
            $this->stock($id, ['barang_unit_id' => $id === 7 ? 2 : 1]);
        }
        $ids = [];
        foreach ([0, 2, 4] as $start) {
            $response = $this->data(['unit' => [1], 'start' => $start, 'length' => 2])->assertOk()->assertJsonPath('recordsFiltered', 6)->assertJsonCount(2, 'data');
            $ids = array_merge($ids, array_column($response->json('data'), 'id'));
        }
        $this->assertSame(range(1, 6), $ids);
        $this->data(['start' => 200, 'length' => 2])->assertJsonCount(0, 'data')->assertJsonPath('recordsFiltered', 7);
        $this->data(['unit' => [2]])->assertJsonPath('data.0.id', 7)->assertJsonPath('recordsTotal', 1);
        foreach (['type' => 2, 'kategori' => 2, 'barang_nama' => 2] as $key => $value) {
            $this->data([$key => $value])->assertJsonPath('recordsFiltered', 0);
        }
    }

    public function test_search_is_server_side_and_does_not_allow_client_order_or_columns_to_change_projection(): void
    {
        $this->stock(1, [], ['kode' => '<script>kode</script>']);
        $this->stock(2, [], ['kode' => 'LAIN']);
        $this->data(['search' => ['value' => 'kode'], 'columns' => [['data' => 'harga_beli', 'search' => ['value' => '70000']]], 'order' => [['column' => 0, 'dir' => 'desc']]])
            ->assertOk()->assertJsonPath('recordsTotal', 2)->assertJsonPath('recordsFiltered', 1)->assertJsonPath('data.0.id', 1)
            ->assertJsonPath('data.0.kode', '&lt;script&gt;kode&lt;/script&gt;');
    }

    public function test_scroller_reads_bounded_batches_and_reload_reflects_current_stock_and_price(): void
    {
        foreach (range(1, 55) as $id) {
            $this->stock($id);
        }
        $this->data()->assertOk()->assertJsonCount(50, 'data')->assertJsonPath('recordsFiltered', 55);
        $this->data(['start' => 50])->assertJsonCount(5, 'data')->assertJsonPath('data.0.id', 51);
        DB::table('barang_stok_hargas')->where('id', 1)->update(['stok' => 3, 'harga' => 200000]);
        $this->data()->assertJsonPath('data.0.stok', 3);
        $this->assertEquals(222000, $this->data()->json('data.0.harga_jual_ppn'));
    }

    public function test_search_treats_wildcards_as_literal_text(): void
    {
        $this->stock(1, [], ['kode' => '100%']);
        $this->stock(2, [], ['kode' => '1000']);
        $this->data(['search' => ['value' => '%']])->assertJsonPath('recordsFiltered', 1)->assertJsonPath('data.0.id', 1);
    }

    public function test_invalid_filter_and_unbounded_scroller_requests_are_rejected(): void
    {
        foreach (['unit' => '1', 'type' => ['1'], 'length' => -1, 'start' => -1, 'search' => ['value' => str_repeat('x', 101)]] as $key => $value) {
            $this->data([$key => $value])->assertUnprocessable();
        }
        $this->data(['length' => 501])->assertUnprocessable();
        $this->getJson(route('user.stok-all', ['mode' => 'invalid']))->assertUnprocessable();
    }

    public function test_page_load_does_not_load_stock_models_and_legacy_mode_remains_available(): void
    {
        $this->stock(1);
        DB::enableQueryLog();
        $this->get(route('user.stok-all'))->assertOk()->assertViewIs('user.stok-all.index')->assertSee('Tampilan lama');
        $queries = collect(DB::getQueryLog())->pluck('query')->implode(' ');
        $this->assertStringNotContainsString('barang_stok_hargas', $queries);
        DB::disableQueryLog();
        $this->get(route('user.stok-all', ['mode' => 'classic']))->assertOk()->assertViewIs('user.stok-all.classic')->assertSee('Tampilan baru');
    }

    public function test_data_endpoint_requires_user_role_and_authentication(): void
    {
        $this->actingAs(new User(['name' => 'Sales', 'role' => 'sales']));
        $this->data()->assertForbidden();
        $this->app['auth']->forgetGuards();
        $this->data()->assertUnauthorized();
    }
}
