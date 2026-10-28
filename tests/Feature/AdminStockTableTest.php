<?php

namespace Tests\Feature;

use App\Models\KasBesar;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class AdminStockTableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['database.default' => 'admin_stock_test', 'database.connections.admin_stock_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
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
            $table->text('keterangan')->nullable();
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
            $table->integer('user_id')->nullable();
            $table->integer('min_jual_ajuan')->default(1);
            $table->timestamps();
        });
        Schema::create('barang_detail_types', function (Blueprint $table): void {
            $table->id();
            $table->integer('barang_id');
        });
        Schema::create('karyawans', function (Blueprint $table): void {
            $table->id();
            $table->boolean('status');
            $table->string('nama')->default('Pegawai');
        });
        Schema::create('pajaks', function (Blueprint $table): void {
            $table->id();
            $table->string('untuk');
            $table->integer('persen');
        });
        DB::table('pajaks')->insert(['untuk' => 'ppn', 'persen' => 11]);
        $this->actingAs(new User(['name' => 'User Uji', 'role' => 'admin']));
    }

    private function stock(int $id, array $stock = [], array $product = []): void
    {
        DB::table('barangs')->insert(array_replace(['id' => $id], $product));
        DB::table('barang_stok_hargas')->insert(array_replace(['id' => $id, 'barang_id' => $id, 'created_at' => '2026-10-01 00:00:00'], $stock));
    }

    private function data(array $parameters = []): TestResponse
    {
        return $this->getJson(route('db.stok-all.data', array_replace(['draw' => 1, 'start' => 0, 'length' => 50], $parameters)));
    }

    public function test_admin_totals_cover_all_filtered_rows_and_preserve_pending_proposal_rules(): void
    {
        $this->stock(1);
        $this->stock(2, ['stok' => 3], ['jenis' => 2]);
        $this->stock(3, ['hide' => 1]);
        DB::table('harga_submissions')->insert(['barang_stok_harga_id' => 1, 'harga_ajuan' => 120000, 'min_jual_ajuan' => 12, 'status' => 1]);
        $response = $this->data(['length' => 1])->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('recordsTotal', 2);
        $this->assertEquals(598500, $response->json('totals.beli'));
        $this->assertEquals(966000, $response->json('totals.jual'));
        $this->assertEquals(367500, $response->json('totals.profit'));
        $this->assertEquals(133200, $response->json('data.0.harga_jual_ppn'));
        $this->data(['search' => ['value' => 'missing']])->assertJsonPath('recordsFiltered', 0)->assertJsonPath('totals.beli', 0);
    }

    public function test_assistant_forces_product_level_zero_price_filter_and_cannot_use_admin_actions(): void
    {
        $this->stock(1);
        DB::table('barang_stok_hargas')->insert(['id' => 2, 'barang_id' => 1, 'harga' => 0, 'hide' => 1]);
        $this->stock(3);
        $this->actingAs(new User(['name' => 'Asisten', 'role' => 'asisten-admin']));
        $this->data(['filter_harga' => 0])->assertOk()->assertJsonPath('recordsTotal', 1)->assertJsonPath('data.0.id', 1);
        $this->getJson(route('db.stok-all.detail', 1))->assertOk()->assertJsonPath('permissions.propose', false)->assertJsonPath('permissions.edit_price', false);
        $this->postJson(route('db.stok-all.price', 1), ['harga' => 120000, 'min_jual' => 1])->assertForbidden();
        $this->postJson(route('db.stok-all.hide', 1))->assertForbidden();
    }

    public function test_page_and_legacy_mode_restrict_user_role_and_do_not_preload_stock_in_new_mode(): void
    {
        $this->stock(1);
        DB::enableQueryLog();
        $this->get(route('db.stok-all'))->assertOk()->assertViewIs('db.stok-all.index')->assertSee('Tampilan lama');
        $this->assertStringNotContainsString('barang_stok_hargas', collect(DB::getQueryLog())->pluck('query')->implode(' '));
        DB::disableQueryLog();
        $this->get(route('db.stok-all', ['mode' => 'classic']))->assertOk()->assertViewIs('db.stok-all.classic')->assertSee('Tampilan baru');
        $this->actingAs(new User(['name' => 'User', 'role' => 'user']));
        $this->get(route('db.stok-all'))->assertForbidden();
        $this->get(route('db.stok-all', ['mode' => 'classic']))->assertForbidden();
        $this->data()->assertForbidden();
    }

    public function test_price_changes_validate_numeric_values_cost_floor_and_stale_revision(): void
    {
        $this->stock(1);
        $revision = $this->getJson(route('db.stok-all.detail', 1))->assertOk()->json('revision');
        $this->postJson(route('db.stok-all.price', 1), ['harga' => 'abc', 'min_jual' => 1, 'revision' => $revision])->assertUnprocessable();
        $this->postJson(route('db.stok-all.price', 1), ['harga' => 1, 'min_jual' => 1, 'revision' => $revision])->assertUnprocessable();
        $this->postJson(route('db.stok-all.price', 1), ['harga' => 120000, 'min_jual' => 12, 'revision' => $revision])->assertOk();
        $this->assertDatabaseHas('barang_stok_hargas', ['id' => 1, 'harga' => 120000, 'min_jual' => 12]);
        $this->postJson(route('db.stok-all.price', 1), ['harga' => 130000, 'min_jual' => 1, 'revision' => $revision])->assertUnprocessable();
    }

    public function test_approval_uses_pending_record_and_cannot_be_applied_twice(): void
    {
        $this->stock(1, ['harga' => 0]);
        $this->actingAs((new User(['name' => 'Asisten', 'role' => 'asisten-admin']))->forceFill(['id' => 8]));
        $revision = $this->getJson(route('db.stok-all.detail', 1))->json('revision');
        $this->postJson(route('db.stok-all.propose', 1), ['harga_ajuan' => 120000, 'min_jual_ajuan' => 12, 'revision' => $revision])->assertOk();
        $this->actingAs(new User(['name' => 'Admin', 'role' => 'admin']));
        $revision = $this->getJson(route('db.stok-all.detail', 1))->json('revision');
        $payload = ['harga_ajuan' => 125000, 'min_jual_ajuan' => 6, 'revision' => $revision];
        $this->postJson(route('db.stok-all.approve', 1), $payload)->assertOk();
        $this->assertDatabaseHas('barang_stok_hargas', ['id' => 1, 'harga' => 125000, 'min_jual' => 6]);
        $this->assertDatabaseHas('harga_submissions', ['barang_stok_harga_id' => 1, 'status' => 2]);
        $this->postJson(route('db.stok-all.approve', 1), $payload)->assertUnprocessable();
    }

    public function test_hiding_preserves_one_visible_batch_and_checks_current_stock(): void
    {
        $this->stock(1, ['stok' => 0]);
        $revision = $this->getJson(route('db.stok-all.detail', 1))->json('revision');
        $this->postJson(route('db.stok-all.hide', 1), ['revision' => $revision])->assertUnprocessable();
        DB::table('barang_stok_hargas')->insert(['id' => 2, 'barang_id' => 1, 'stok' => 0]);
        $this->postJson(route('db.stok-all.hide', 1), ['revision' => $revision])->assertOk();
        $this->assertDatabaseHas('barang_stok_hargas', ['id' => 1, 'hide' => 1]);
        $this->stock(3);
        $revision = $this->getJson(route('db.stok-all.detail', 3))->json('revision');
        $this->postJson(route('db.stok-all.hide', 3), ['revision' => $revision])->assertUnprocessable();
    }

    public function test_only_superuser_can_correct_stock_and_missing_history_is_handled(): void
    {
        $this->stock(1);
        $revision = $this->getJson(route('db.stok-all.detail', 1))->json('revision');
        $payload = ['stok_awal' => 20, 'stok' => 10, 'harga_beli' => 80000, 'revision' => $revision];
        $this->postJson(route('db.stok-all.correct', 1), $payload)->assertForbidden();
        $this->actingAs(new User(['name' => 'SU', 'role' => 'su']));
        $this->postJson(route('db.stok-all.correct', 1), $payload)->assertOk();
        $this->assertDatabaseHas('barang_stok_hargas', ['id' => 1, 'stok' => 10, 'harga_beli' => 80000]);
        $this->getJson(route('db.stok-all.history', 999))->assertNotFound();
        $this->getJson(route('db.stok-all.history', 1))->assertOk()->assertJsonPath('data.0.stok_awal', 20);
    }

    public function test_loss_rejects_changed_stock_invalid_employee_and_tampered_quantity_before_writes(): void
    {
        $this->stock(1);
        $revision = $this->getJson(route('db.stok-all.detail', 1))->json('revision');
        $this->postJson(route('db.stok-all.loss', 1), ['revision' => $revision, 'karyawan_id' => 999, 'jumlah_hilang' => 6, 'aksi' => 1])->assertUnprocessable();
        $this->assertDatabaseHas('barang_stok_hargas', ['id' => 1, 'stok' => 5]);
    }

    #[TestWith(['admin'])]
    #[TestWith(['asisten-admin'])]
    #[TestWith(['user'])]
    #[TestWith(['sales'])]
    public function test_non_superuser_cannot_correct_stock_through_new_or_legacy_endpoint(string $role): void
    {
        $this->stock(1);
        $revision = $this->getJson(route('db.stok-all.detail', 1))->json('revision');
        $this->actingAs(new User(['name' => 'Pengguna Uji', 'role' => $role]));
        $payload = ['revision' => $revision, 'stok_awal' => 99, 'stok' => 99, 'harga_beli' => 1];

        $this->postJson(route('db.stok-all.correct', 1), $payload)->assertForbidden();
        $this->patchJson(route('db.stok.edit-stok-su', 1), $payload)->assertForbidden();

        $this->assertDatabaseHas('barang_stok_hargas', ['id' => 1, 'stok_awal' => 10, 'stok' => 5, 'harga_beli' => 70000]);
        if (in_array($role, ['admin', 'asisten-admin'], true)) {
            $this->get(route('db.stok-all'))->assertViewHas('stockConfig', fn (array $config): bool => $config['superuser'] === false);
        }
    }

    public function test_stock_correction_preserves_existing_fractional_purchase_cost_when_not_changed(): void
    {
        $this->stock(1, ['harga_beli' => 70000.55]);
        $this->actingAs(new User(['name' => 'SU', 'role' => 'su']));
        $revision = $this->getJson(route('db.stok-all.detail', 1))->json('revision');
        $this->postJson(route('db.stok-all.correct', 1), ['revision' => $revision, 'stok_awal' => 20, 'stok' => 15])->assertOk();
        $this->assertEquals(70000.55, DB::table('barang_stok_hargas')->where('id', 1)->value('harga_beli'));
    }

    public function test_legacy_price_form_uses_the_same_validated_mutation_service(): void
    {
        $this->stock(1);
        $this->patch(route('db.stok-ppn.store', 1), ['harga' => '125.000', 'min_jual' => '12'])->assertRedirect();
        $this->assertDatabaseHas('barang_stok_hargas', ['id' => 1, 'harga' => 125000, 'min_jual' => 12]);
        $this->patchJson(route('db.stok-ppn.store', 1), ['harga' => 'abc', 'min_jual' => 1])->assertUnprocessable();
    }

    #[TestWith([1])]
    #[TestWith([2])]
    #[TestWith([1, false])]
    public function test_loss_preserves_ppn_cash_rule_and_sends_notification_only_after_commit(int $action, bool $groupExists = true): void
    {
        $this->stock(1, [], ['jenis' => 2]);
        DB::table('karyawans')->insert(['id' => 1, 'status' => 1, 'nama' => 'Pegawai Uji']);
        Schema::create('rekenings', function (Blueprint $table): void {
            $table->id();
            $table->string('untuk');
            $table->string('bank');
            $table->string('nama_rek');
            $table->string('no_rek');
        });
        DB::table('rekenings')->insert(['untuk' => 'kas-besar-ppn', 'bank' => 'Bank', 'nama_rek' => 'Rekening', 'no_rek' => '123']);
        Schema::create('group_was', function (Blueprint $table): void {
            $table->id();
            $table->string('untuk');
            $table->string('nama_group');
        });
        if ($groupExists) {
            DB::table('group_was')->insert(['untuk' => 'kas-besar-ppn', 'nama_group' => 'Grup Uji']);
        }
        Schema::create('kas_besars', function (Blueprint $table): void {
            $table->id();
            $table->boolean('ppn_kas');
            $table->boolean('jenis');
            $table->string('uraian');
            foreach (['nominal', 'saldo', 'modal_investor_terakhir'] as $column) {
                $table->bigInteger($column);
            }
            $table->string('bank');
            $table->string('nama_rek');
            $table->string('no_rek');
            $table->timestamps();
        });
        DB::table('kas_besars')->insert(['ppn_kas' => 1, 'jenis' => 1, 'uraian' => 'Saldo awal', 'nominal' => 500000, 'saldo' => 500000, 'modal_investor_terakhir' => 1000, 'bank' => '-', 'nama_rek' => '-', 'no_rek' => '-']);
        Schema::create('ganti_rugis', function (Blueprint $table): void {
            $table->id();
            foreach (['harga', 'jumlah', 'total', 'karyawan_id', 'kas_ppn', 'lunas', 'total_bayar', 'sisa', 'barang_stok_harga_id', 'barang_id'] as $column) {
                $table->bigInteger($column);
            }
            $table->timestamps();
        });
        $messages = (object) ['items' => []];
        $this->app->instance('stock-test-messages', $messages);
        $this->app->bind(KasBesar::class, fn () => new class extends KasBesar
        {
            protected $table = 'kas_besars';

            public function sendWa($tujuan, $pesan): void
            {
                app('stock-test-messages')->items[] = ['level' => DB::transactionLevel(), 'target' => $tujuan, 'body' => $pesan];
            }
        });
        $revision = $this->getJson(route('db.stok-all.detail', 1))->json('revision');
        $payload = ['revision' => $revision, 'karyawan_id' => 1, 'jumlah_hilang' => 2, 'aksi' => $action, 'kas_ppn' => 0, 'harga_beli_dpp_act' => 1];

        if (! $groupExists) {
            $this->postJson(route('db.stok-all.loss', 1), $payload)->assertUnprocessable();
            $this->assertDatabaseHas('barang_stok_hargas', ['id' => 1, 'stok' => 5]);
            $this->assertSame(0, DB::table('ganti_rugis')->count());
            $this->assertSame(1, DB::table('kas_besars')->count());
            $this->assertSame([], $messages->items);

            return;
        }
        $this->postJson(route('db.stok-all.loss', 1), $payload)->assertOk();

        $this->assertDatabaseHas('barang_stok_hargas', ['id' => 1, 'stok' => 3]);
        $this->assertDatabaseHas('ganti_rugis', ['barang_id' => 1, 'kas_ppn' => 1, 'harga' => 77700, 'total' => 155400, 'lunas' => $action === 1 ? 1 : 0]);
        $this->assertEquals($action === 1 ? 655400 : 500000, DB::table('kas_besars')->orderByDesc('id')->value('saldo'));
        $this->assertCount($action === 1 ? 1 : 2, $messages->items);
        foreach ($messages->items as $message) {
            $this->assertSame(0, $message['level']);
        }
        $this->postJson(route('db.stok-all.loss', 1), $payload)->assertUnprocessable();
        $this->assertSame(1, DB::table('ganti_rugis')->count());
    }
}
