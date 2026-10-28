<?php

namespace Tests\Feature;

use App\Models\db\Konsumen;
use App\Models\db\KonsumenDoc;
use App\Models\User;
use Database\Factories\KonsumenFactory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerBiodataTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['database.default' => 'customer_test', 'database.connections.customer_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        Schema::create('invoice_juals', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('konsumen_id');
            $table->unsignedBigInteger('karyawan_id')->nullable();
            $table->boolean('lunas')->default(false);
            $table->boolean('void')->default(false);
            $table->timestamps();
        });
        Schema::create('kode_tokos', function (Blueprint $table): void {
            $table->id();
            $table->string('kode');
        });
        Schema::create('jabatans', function (Blueprint $table): void {
            $table->id();
            $table->boolean('is_sales');
        });
        Schema::create('karyawans', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('jabatan_id');
            $table->boolean('status');
            $table->string('nama')->default('Sales Uji');
        });
        Schema::create('wilayahs', function (Blueprint $table): void {
            $table->id();
            $table->string('id_wilayah');
            $table->string('id_induk_wilayah')->nullable();
            $table->integer('id_level_wilayah');
            $table->string('nama_wilayah')->default('Wilayah Uji');
        });
        Schema::create('konsumens', function (Blueprint $table): void {
            $table->id();
            $table->integer('kode')->unique();
            foreach (['kode_toko_id', 'karyawan_id', 'provinsi_id', 'kabupaten_kota_id', 'kecamatan_id'] as $column) {
                $table->unsignedBigInteger($column)->nullable();
            }
            foreach (['nama', 'cp', 'no_hp', 'no_kantor', 'npwp', 'nik', 'alamat', 'alasan', 'upload_ktp'] as $column) {
                $table->string($column)->nullable();
            }
            $table->integer('pembayaran');
            $table->integer('tempo_hari')->nullable();
            $table->integer('plafon')->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('checklist_kunjungan')->default(true);
            $table->timestamps();
        });
        Schema::create('konsumen_plafon_histories', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('konsumen_id');
            $table->bigInteger('nominal_lama');
            $table->bigInteger('nominal_baru');
            $table->string('keterangan')->nullable();
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();
        });
        DB::table('kode_tokos')->insert(['id' => 1, 'kode' => 'TB']);
        DB::table('jabatans')->insert(['id' => 1, 'is_sales' => true]);
        DB::table('karyawans')->insert(['id' => 1, 'jabatan_id' => 1, 'status' => true]);
        DB::table('wilayahs')->insert([
            ['id' => 1, 'id_wilayah' => 'P', 'id_induk_wilayah' => null, 'id_level_wilayah' => 1],
            ['id' => 2, 'id_wilayah' => 'K', 'id_induk_wilayah' => 'P', 'id_level_wilayah' => 2],
            ['id' => 3, 'id_wilayah' => 'C', 'id_induk_wilayah' => 'K', 'id_level_wilayah' => 3],
            ['id' => 4, 'id_wilayah' => 'OTHER', 'id_induk_wilayah' => 'X', 'id_level_wilayah' => 2],
        ]);
        $user = new User(['name' => 'Admin', 'role' => 'admin']);
        $user->id = 999;
        $this->actingAs($user);
    }

    private function payload(array $overrides = []): array
    {
        return array_replace(['kode_toko_id' => 1, 'nama' => 'Toko Uji', 'cp' => 'Kontak', 'no_hp' => '081234567890', 'npwp' => '-', 'nik' => null,
            'provinsi_id' => 1, 'kabupaten_kota_id' => 2, 'kecamatan_id' => 3, 'alamat' => 'Alamat uji', 'pembayaran' => 1, 'karyawan_id' => 1], $overrides);
    }

    public function test_sales_changes_transfer_only_unpaid_non_void_invoices_through_both_forms(): void
    {
        (require database_path('migrations/2026_10_27_215529_create_konsumen_sales_histories_table.php'))->up();
        DB::table('karyawans')->insert(['id' => 2, 'jabatan_id' => 1, 'status' => 1, 'nama' => 'Sales Baru']);
        foreach (['db.konsumen.update_sales', 'db.konsumen.update'] as $route) {
            $customer = KonsumenFactory::new()->create($this->payload());
            $other = KonsumenFactory::new()->create($this->payload());
            $eligible = DB::table('invoice_juals')->insertGetId(['konsumen_id' => $customer->id, 'karyawan_id' => 1, 'lunas' => 0, 'void' => 0]);
            $unassigned = DB::table('invoice_juals')->insertGetId(['konsumen_id' => $customer->id, 'karyawan_id' => null, 'lunas' => 0, 'void' => 0]);
            $paid = DB::table('invoice_juals')->insertGetId(['konsumen_id' => $customer->id, 'karyawan_id' => 1, 'lunas' => 1, 'void' => 0]);
            $void = DB::table('invoice_juals')->insertGetId(['konsumen_id' => $customer->id, 'karyawan_id' => 1, 'lunas' => 0, 'void' => 1]);
            $otherInvoice = DB::table('invoice_juals')->insertGetId(['konsumen_id' => $other->id, 'karyawan_id' => 1, 'lunas' => 0, 'void' => 0]);
            $data = ['_original_sales_id' => 1, 'karyawan_id' => 2];
            if ($route === 'db.konsumen.update') {
                $data += $this->payload();
            }
            $this->patchJson(route($route, $customer), $data)->assertStatus($route === 'db.konsumen.update' ? 302 : 200);
            foreach ([$eligible, $unassigned] as $id) {
                $this->assertDatabaseHas('invoice_juals', ['id' => $id, 'karyawan_id' => 2]);
            }
            foreach ([$paid, $void, $otherInvoice] as $id) {
                $this->assertDatabaseHas('invoice_juals', ['id' => $id, 'karyawan_id' => 1]);
            }
            DB::table('invoice_juals')->where('id', $eligible)->update(['karyawan_id' => 1]);
            $data['_original_sales_id'] = 2;
            $this->patchJson(route($route, $customer), $data)->assertStatus($route === 'db.konsumen.update' ? 302 : 200);
            $this->assertDatabaseHas('invoice_juals', ['id' => $eligible, 'karyawan_id' => 1]);
        }
        $this->assertDatabaseCount('konsumen_sales_histories', 2);
    }

    public function test_invoice_transfer_failure_rolls_back_customer_and_sales_history(): void
    {
        (require database_path('migrations/2026_10_27_215529_create_konsumen_sales_histories_table.php'))->up();
        DB::table('karyawans')->insert(['id' => 2, 'jabatan_id' => 1, 'status' => 1, 'nama' => 'Sales Baru']);
        $customer = KonsumenFactory::new()->create($this->payload());
        $invoice = DB::table('invoice_juals')->insertGetId(['konsumen_id' => $customer->id, 'karyawan_id' => 1]);
        DB::statement("CREATE TRIGGER reject_invoice_transfer BEFORE UPDATE OF karyawan_id ON invoice_juals BEGIN SELECT RAISE(ABORT, 'Transfer failure'); END");
        foreach (['db.konsumen.update_sales', 'db.konsumen.update'] as $route) {
            $data = ['_original_sales_id' => 1, 'karyawan_id' => 2];
            if ($route === 'db.konsumen.update') {
                $data += $this->payload();
            }
            $this->patchJson(route($route, $customer), $data)->assertStatus(500);
            $this->assertSame(1, (int) $customer->fresh()->karyawan_id);
            $this->assertDatabaseHas('invoice_juals', ['id' => $invoice, 'karyawan_id' => 1]);
            $this->assertDatabaseCount('konsumen_sales_histories', 0);
        }
    }

    public function test_stale_sales_updates_are_rejected_in_both_forms(): void
    {
        (require database_path('migrations/2026_10_27_215529_create_konsumen_sales_histories_table.php'))->up();
        DB::table('karyawans')->insert(['id' => 2, 'jabatan_id' => 1, 'status' => 1, 'nama' => 'Sales Baru']);
        $customer = KonsumenFactory::new()->create($this->payload());
        $this->patchJson(route('db.konsumen.update_sales', $customer), ['karyawan_id' => 2, '_original_sales_id' => 1])->assertOk();
        $this->patch(route('db.konsumen.update', $customer), ['_original_sales_id' => 1] + $this->payload(['nama' => 'Stale']))->assertSessionHasErrors('karyawan_id');
        $this->patchJson(route('db.konsumen.update_sales', $customer), ['_original_sales_id' => 1, 'karyawan_id' => 1])->assertUnprocessable();
        $this->patchJson(route('db.konsumen.update_sales', $customer), ['karyawan_id' => 1])->assertUnprocessable()->assertJsonValidationErrors('_original_sales_id');
        $this->assertSame(2, (int) $customer->fresh()->karyawan_id);
        $this->assertSame('Toko Uji', $customer->fresh()->nama);
        $this->assertDatabaseCount('konsumen_sales_histories', 1);
    }

    public function test_store_codes_validate_text_and_cannot_delete_used_code(): void
    {
        Schema::table('kode_tokos', function (Blueprint $table): void {
            $table->timestamps();
        });
        $this->postJson(route('db.kode-toko.store'), ['kode' => '<img src=x onerror=alert(1)>'])->assertUnprocessable();
        $this->postJson(route('db.kode-toko.store'), ['kode' => ['bad']])->assertUnprocessable();
        $this->postJson(route('db.kode-toko.store'), ['kode' => str_repeat('a', 256)])->assertUnprocessable();
        $this->postJson(route('db.kode-toko.store'), ['kode' => 'BARU'])->assertOk();
        $new = DB::table('kode_tokos')->where('kode', 'BARU')->value('id');
        $this->patchJson(route('db.kode-toko.update', 1), ['kode' => '<script>'])->assertUnprocessable();
        $customer = KonsumenFactory::new()->create($this->payload());
        $this->deleteJson(route('db.kode-toko.delete', 1))->assertUnprocessable()->assertJsonValidationErrors('kode');
        $this->assertSame(1, (int) $customer->fresh()->kode_toko_id);
        $this->assertDatabaseHas('kode_tokos', ['id' => 1]);
        $this->deleteJson(route('db.kode-toko.delete', $new))->assertOk();
        $this->assertDatabaseMissing('kode_tokos', ['id' => $new]);
    }

    public function test_document_database_failures_preserve_files_and_cleanup_failed_uploads(): void
    {
        Storage::fake('local');
        Schema::create('konsumen_docs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('konsumen_id');
            $table->string('nama');
            $table->unsignedBigInteger('barang_unit_id')->nullable();
            $table->string('file_path');
            $table->timestamps();
        });
        $customer = KonsumenFactory::new()->create($this->payload());
        KonsumenDoc::creating(function (): void {
            throw new \RuntimeException('private database details');
        });
        $response = $this->postJson(route('db.konsumen.dokumen.store'), ['konsumen_id' => $customer->id, 'nama' => 'Dokumen', 'file' => UploadedFile::fake()->create('doc.pdf', 5, 'application/pdf')]);
        $response->assertStatus(500);
        $this->assertStringNotContainsString('private database details', $response->getContent());
        $this->assertSame([], Storage::disk('local')->allFiles());
        KonsumenDoc::flushEventListeners();
        foreach (['admin' => 'db.konsumen', 'user' => 'user.konsumen'] as $role => $prefix) {
            $this->actingAs(new User(['id' => 999, 'role' => $role]));
            $this->postJson(route($prefix.'.dokumen.store'), ['konsumen_id' => $customer->id, 'nama' => 'Dokumen', 'file' => UploadedFile::fake()->create('doc.pdf', 5, 'application/pdf')])->assertOk();
            $doc = KonsumenDoc::sole();
            $path = $doc->file_path;
            Storage::disk('local')->assertExists($path);
            KonsumenDoc::deleting(function (): void {
                throw new \RuntimeException('private deletion details');
            });
            $deleteRoute = $role === 'admin' ? 'db.konsumen.dokumen.destroy' : '.konsumen.dokumen.destroy';
            $this->deleteJson(route($deleteRoute, $doc))->assertStatus(500);
            Storage::disk('local')->assertExists($path);
            $this->assertDatabaseHas('konsumen_docs', ['id' => $doc->id]);
            KonsumenDoc::flushEventListeners();
            $this->deleteJson(route($deleteRoute, $doc))->assertOk();
            Storage::disk('local')->assertMissing($path);
        }
    }

    public function test_sales_can_be_changed_independently_with_history_and_validation(): void
    {
        (require database_path('migrations/2026_10_27_215529_create_konsumen_sales_histories_table.php'))->up();
        DB::table('karyawans')->insert(['id' => 2, 'jabatan_id' => 1, 'status' => 1, 'nama' => 'Sales Baru']);
        $customer = KonsumenFactory::new()->create($this->payload());
        $url = route('db.konsumen.update_sales', $customer);
        $this->patchJson($url, ['_original_sales_id' => $customer->fresh()->karyawan_id, 'karyawan_id' => 2, 'sales_change_reason' => 'Alih area', 'nama' => 'Tidak boleh berubah', 'plafon' => 999])->assertOk();
        $this->assertSame('Toko Uji', $customer->fresh()->nama);
        $this->assertSame(2, (int) $customer->fresh()->karyawan_id);
        $this->assertSame(0, (int) $customer->fresh()->plafon);
        $this->assertDatabaseHas('konsumen_sales_histories', ['konsumen_id' => $customer->id, 'sales_lama_id' => 1, 'sales_baru_id' => 2, 'updated_by' => 999, 'keterangan' => 'Alih area']);
        $this->patchJson($url, ['_original_sales_id' => $customer->fresh()->karyawan_id, 'karyawan_id' => 2])->assertOk();
        $this->assertDatabaseCount('konsumen_sales_histories', 1);
        $this->patchJson($url, ['_original_sales_id' => $customer->fresh()->karyawan_id, 'karyawan_id' => 1, 'sales_change_reason' => str_repeat('a', 501)])->assertUnprocessable()->assertJsonValidationErrors('sales_change_reason');
        DB::table('karyawans')->where('id', 1)->update(['status' => 0]);
        $this->patchJson($url, ['_original_sales_id' => $customer->fresh()->karyawan_id, 'karyawan_id' => 1])->assertUnprocessable()->assertJsonValidationErrors('karyawan_id');
        DB::table('jabatans')->where('id', 1)->update(['is_sales' => 0]);
        $this->patchJson($url, ['_original_sales_id' => $customer->fresh()->karyawan_id, 'karyawan_id' => 2])->assertUnprocessable()->assertJsonValidationErrors('karyawan_id');
        $this->assertDatabaseCount('konsumen_sales_histories', 1);
        $this->actingAs(new User(['id' => 998, 'role' => 'sales']))->patchJson($url, ['karyawan_id' => 2])->assertForbidden();
    }

    public function test_sales_history_only_records_changes_and_preserves_names(): void
    {
        (require database_path('migrations/2026_10_27_215529_create_konsumen_sales_histories_table.php'))->up();
        DB::table('karyawans')->insert(['id' => 2, 'jabatan_id' => 1, 'status' => 1, 'nama' => 'Sales Baru']);
        $customer = KonsumenFactory::new()->create($this->payload());
        $url = route('db.konsumen.update', $customer);
        $this->patch($url, ['_original_sales_id' => $customer->fresh()->karyawan_id] + $this->payload())->assertSessionHasNoErrors();
        $this->assertDatabaseCount('konsumen_sales_histories', 0);
        $this->patch($url, ['_original_sales_id' => $customer->fresh()->karyawan_id] + $this->payload(['karyawan_id' => 2, 'sales_change_reason' => 'Pembagian area']))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('konsumen_sales_histories', ['konsumen_id' => $customer->id, 'sales_lama_id' => 1, 'sales_baru_id' => 2, 'sales_baru' => 'Sales Baru', 'updated_by' => 999, 'keterangan' => 'Pembagian area']);
        $this->assertSame(2, (int) $customer->fresh()->karyawan_id);
        $this->patch($url, ['_original_sales_id' => $customer->fresh()->karyawan_id] + $this->payload(['karyawan_id' => 2]))->assertSessionHasNoErrors();
        $this->assertDatabaseCount('konsumen_sales_histories', 1);
        DB::table('karyawans')->where('id', 2)->update(['nama' => 'Nama Berubah']);
        $this->getJson(route('db.konsumen.histori_sales', $customer))->assertOk()->assertJsonPath('data.0.sales_baru', 'Sales Baru');
        $this->patch($url, ['_original_sales_id' => $customer->fresh()->karyawan_id] + $this->payload(['karyawan_id' => 1]))->assertSessionHasNoErrors();
        $this->assertDatabaseCount('konsumen_sales_histories', 2);
        $this->patch($url, ['_original_sales_id' => $customer->fresh()->karyawan_id] + $this->payload(['karyawan_id' => 2, 'sales_change_reason' => str_repeat('a', 501)]))->assertSessionHasErrors('sales_change_reason');
        $this->assertSame(1, (int) $customer->fresh()->karyawan_id);
        $this->assertDatabaseCount('konsumen_sales_histories', 2);
        $this->actingAs(new User(['id' => 998, 'role' => 'sales']))->getJson(route('db.konsumen.histori_sales', $customer))->assertForbidden();
    }

    public function test_cash_can_be_created_without_credit_fields_and_tempo_requires_canonical_amount(): void
    {
        $this->post(route('db.konsumen.store'), $this->payload())->assertSessionHasNoErrors();
        $this->assertSame(0, (int) Konsumen::sole()->plafon);
        $this->post(route('db.konsumen.store'), $this->payload(['pembayaran' => 2]))->assertSessionHasErrors(['plafon', 'tempo_hari']);
        $this->post(route('db.konsumen.store'), $this->payload(['pembayaran' => 2, 'plafon' => '1.000', 'tempo_hari' => 30]))->assertSessionHasErrors('plafon');
        $this->post(route('db.konsumen.store'), $this->payload(['pembayaran' => 2, 'plafon' => '1000000', 'tempo_hari' => 30]))->assertSessionHasNoErrors();
        $this->assertSame([1, 2], Konsumen::orderBy('id')->pluck('kode')->all());
    }

    public function test_invalid_payment_sales_and_geographic_hierarchy_are_rejected(): void
    {
        foreach ([['pembayaran' => 9], ['kabupaten_kota_id' => 4], ['nik' => '123'], ['no_hp' => '<script>']] as $values) {
            $this->post(route('db.konsumen.store'), $this->payload($values))->assertSessionHasErrors(array_key_first($values));
        }
        DB::table('jabatans')->where('id', 1)->update(['is_sales' => false]);
        $this->post(route('db.konsumen.store'), $this->payload())->assertSessionHasErrors('karyawan_id');
    }

    public function test_deactivation_requires_reason_and_disables_checklist_without_deleting_customer(): void
    {
        $customer = KonsumenFactory::new()->create($this->payload() + ['kode' => 7]);
        $url = route('db.konsumen.delete', $customer);
        $this->delete($url, ['active' => 0])->assertSessionHasErrors('alasan');
        $this->delete($url, ['active' => 0, 'alasan' => 'Toko tutup'])->assertSessionHasNoErrors();
        $this->assertSame(0, (int) $customer->fresh()->active);
        $this->assertSame(0, (int) $customer->fresh()->checklist_kunjungan);
        $this->delete($url, ['active' => 0, 'alasan' => 'Toko tutup'])->assertSessionHasNoErrors();
        $this->assertSame(0, (int) $customer->fresh()->active);
        $this->delete($url, ['active' => 1])->assertSessionHasNoErrors();
        $this->assertSame(1, (int) $customer->fresh()->active);
    }

    public function test_plafon_changes_validate_integer_and_always_close_transaction(): void
    {
        $customer = KonsumenFactory::new()->create($this->payload(['pembayaran' => 2, 'plafon' => 100000, 'tempo_hari' => 30]) + ['kode' => 8]);
        $url = route('db.konsumen.update_plafon', $customer->id);
        $this->postJson($url, ['plafon' => -1])->assertUnprocessable();
        $this->postJson($url, ['plafon' => '200.000'])->assertUnprocessable();
        $this->postJson($url, ['plafon' => 100000])->assertOk();
        $this->assertSame(0, DB::transactionLevel());
        $this->postJson($url, ['plafon' => 200000, 'keterangan' => 'Penyesuaian limit'])->assertOk();
        $this->assertDatabaseHas('konsumen_plafon_histories', ['konsumen_id' => $customer->id, 'nominal_lama' => 100000, 'nominal_baru' => 200000, 'updated_by' => 999, 'keterangan' => 'Penyesuaian limit']);
    }

    public function test_edit_preserves_legacy_identity_and_does_not_accept_plafon_override(): void
    {
        $customer = KonsumenFactory::new()->create($this->payload(['pembayaran' => 2, 'plafon' => 500000, 'tempo_hari' => 30, 'nik' => 'LEGACY', 'no_kantor' => '0']) + ['kode' => 9]);
        $this->patch(route('db.konsumen.update', $customer), ['_original_sales_id' => $customer->karyawan_id] + $this->payload(['nik' => 'LEGACY', 'no_kantor' => '0', 'plafon' => 999999]))->assertSessionHasNoErrors();
        $this->assertSame(500000, (int) $customer->fresh()->plafon);
        $this->assertSame(0, (int) $customer->fresh()->tempo_hari);
    }

    public function test_biodata_page_renders_and_safely_displays_contact_html(): void
    {
        Schema::create('konsumen_docs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('konsumen_id');
            $table->string('file_path')->nullable();
            $table->unsignedBigInteger('barang_unit_id')->nullable();
            $table->string('nama')->nullable();
            $table->timestamps();
        });
        KonsumenFactory::new()->create($this->payload(['cp' => '<img src=x onerror=alert(1)>']) + ['kode' => 10]);
        $this->get(route('db.konsumen'))->assertOk()->assertSee('Biodata konsumen')->assertSee('customer-biodata.js');
        $response = $this->getJson(route('db.konsumen.data', ['draw' => 1, 'length' => 10]));
        $response->assertOk()->assertJsonCount(1, 'data');
        $this->assertStringNotContainsString('<img', $response->json('data.0.cp'));
        $this->assertStringContainsString('&lt;img', $response->json('data.0.cp'));
        $this->actingAs(new User(['id' => 999, 'role' => 'user']));
        $userResponse = $this->getJson(route('user.konsumen.data', ['draw' => 1, 'length' => 10]))->assertOk();
        $this->assertStringNotContainsString('<img', $userResponse->json('data.0.cp'));
        $this->assertStringContainsString('&lt;img', $userResponse->json('data.0.cp'));
    }

    public function test_private_customer_files_require_authorized_login_and_handle_missing_files(): void
    {
        Storage::fake('local');
        $customer = KonsumenFactory::new()->create($this->payload() + ['kode' => 11]);
        $this->post(route('db.konsumen.upload-ktp', $customer), ['upload_ktp' => UploadedFile::fake()->image('ktp.jpg')])->assertSessionHasNoErrors();
        $path = $customer->fresh()->upload_ktp;
        Storage::disk('local')->assertExists($path);
        $url = route('customer-files.ktp', $customer);
        $this->get($url)->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->app['auth']->forgetGuards();
        $this->get($url)->assertRedirect(route('login'));
        $sales = new User(['name' => 'Sales', 'role' => 'sales']);
        $sales->id = 88;
        $this->actingAs($sales);
        $this->get($url)->assertForbidden();
    }

    public function test_deployment_file_migration_moves_verifies_and_can_be_repeated(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        Schema::create('konsumen_docs', function (Blueprint $table): void {
            $table->id();
            $table->string('file_path');
        });
        $customer = KonsumenFactory::new()->create($this->payload() + ['kode' => 12, 'upload_ktp' => 'konsumen/legacy.jpg']);
        DB::table('konsumen_docs')->insert(['file_path' => 'konsumen_docs/legacy.pdf']);
        Storage::disk('public')->put('konsumen/legacy.jpg', 'original image');
        Storage::disk('public')->put('konsumen_docs/legacy.pdf', 'original pdf');
        $migration = require database_path('migrations/2026_10_27_212036_protect_existing_customer_files.php');
        $migration->up();
        $migration->up();
        $this->assertSame('original image', Storage::disk('local')->get($customer->upload_ktp));
        $this->assertSame('original pdf', Storage::disk('local')->get('konsumen_docs/legacy.pdf'));
        Storage::disk('public')->assertMissing($customer->upload_ktp);
        Storage::disk('public')->assertMissing('konsumen_docs/legacy.pdf');
    }

    public function test_file_migration_never_overwrites_a_different_private_copy(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        Schema::create('konsumen_docs', function (Blueprint $table): void {
            $table->id();
            $table->string('file_path');
        });
        KonsumenFactory::new()->create($this->payload() + ['kode' => 15, 'upload_ktp' => 'konsumen/conflict.jpg']);
        Storage::disk('public')->put('konsumen/conflict.jpg', 'public original');
        Storage::disk('local')->put('konsumen/conflict.jpg', 'different private content');
        try {
            $this->artisan('customers:protect-files')->run();
            $this->fail('Conflicting files must stop migration.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('differs', $exception->getMessage());
        }
        $this->assertSame('public original', Storage::disk('public')->get('konsumen/conflict.jpg'));
        $this->assertSame('different private content', Storage::disk('local')->get('konsumen/conflict.jpg'));
    }
}
