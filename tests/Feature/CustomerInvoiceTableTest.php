<?php

namespace Tests\Feature;

use App\Models\transaksi\InvoiceJual;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class CustomerInvoiceTableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->travelTo(Carbon::parse('2026-10-29 12:00:00'));
        config(['database.default' => 'customer_invoice_table_test', 'database.connections.customer_invoice_table_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        Schema::create('kode_tokos', function (Blueprint $table): void {
            $table->id();
            $table->string('kode');
        });
        Schema::create('wilayahs', function (Blueprint $table): void {
            $table->id();
            $table->string('nama_wilayah');
            $table->string('id_wilayah')->nullable();
            $table->string('id_induk_wilayah')->nullable();
        });
        Schema::create('karyawans', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
        });
        Schema::create('konsumens', function (Blueprint $table): void {
            $table->id();
            $table->integer('kode');
            $table->string('nama');
            $table->integer('kode_toko_id')->nullable();
            $table->integer('kecamatan_id')->nullable();
            $table->integer('kabupaten_kota_id')->nullable();
            $table->integer('plafon')->default(1000000);
            $table->integer('pembayaran')->default(2);
            $table->string('upload_ktp')->nullable();
        });
        Schema::create('invoice_juals', function (Blueprint $table): void {
            $table->id();
            $table->integer('konsumen_id')->nullable();
            $table->integer('karyawan_id')->nullable();
            $table->integer('nomor')->default(1);
            $table->string('kode');
            foreach (['total', 'diskon', 'ppn', 'add_fee', 'grand_total', 'dp', 'dp_ppn', 'sisa_ppn', 'sisa_tagihan'] as $field) {
                $table->integer($field)->default(0);
            }
            foreach (['void', 'titipan', 'lunas', 'kas_ppn', 'ppn_dipungut'] as $field) {
                $table->boolean($field)->default(false);
            }
            $table->integer('sistem_pembayaran')->default(2);
            $table->date('jatuh_tempo')->default('2026-10-20');
            $table->string('internal_note')->nullable();
            $table->timestamps();
        });
        Schema::create('invoice_jual_cicils', function (Blueprint $table): void {
            $table->id();
            $table->integer('invoice_jual_id');
            $table->integer('nominal');
            $table->integer('ppn')->default(0);
            $table->timestamps();
        });
        DB::table('kode_tokos')->insert([['id' => 1, 'kode' => 'TK'], ['id' => 2, 'kode' => 'CV']]);
        DB::table('wilayahs')->insert([
            ['id' => 1, 'nama_wilayah' => 'Palembang'], ['id' => 2, 'nama_wilayah' => 'Ilir Barat'],
            ['id' => 3, 'nama_wilayah' => 'Banyuasin'], ['id' => 4, 'nama_wilayah' => 'Talang Kelapa'],
        ]);
        DB::table('karyawans')->insert([['id' => 1, 'nama' => 'Sales Alpha'], ['id' => 2, 'nama' => 'Sales Beta']]);
        DB::table('konsumens')->insert([
            ['id' => 1, 'kode' => 2, 'nama' => 'Toko Satu', 'kode_toko_id' => 1, 'kabupaten_kota_id' => 1, 'kecamatan_id' => 2, 'upload_ktp' => 'private-identity.jpg'],
            ['id' => 2, 'kode' => 3, 'nama' => 'Toko Dua', 'kode_toko_id' => 2, 'kabupaten_kota_id' => 3, 'kecamatan_id' => 4, 'upload_ktp' => null],
        ]);
        $this->actingAs(new User(['name' => 'Admin Uji', 'role' => 'admin']));
    }

    private function invoice(int $id, array $attributes = []): void
    {
        DB::table('invoice_juals')->insert(array_replace([
            'id' => $id, 'konsumen_id' => 1, 'karyawan_id' => 1, 'nomor' => $id, 'kode' => 'INV-'.$id,
            'total' => 100000, 'grand_total' => 100000, 'sisa_tagihan' => 100000,
            'created_at' => '2026-10-01 08:00:00', 'updated_at' => '2026-10-02 08:00:00',
            'internal_note' => 'DO-NOT-SERIALIZE-INTERNAL-NOTE',
        ], $attributes));
    }

    private function installment(int $invoice, int $nominal, int $tax = 0): void
    {
        DB::table('invoice_jual_cicils')->insert([
            'invoice_jual_id' => $invoice, 'nominal' => $nominal, 'ppn' => $tax,
            'created_at' => '2026-10-10 09:00:00', 'updated_at' => '2026-10-10 09:00:00',
        ]);
    }

    private function data(array $parameters = []): TestResponse
    {
        return $this->getJson('/billing/invoice-konsumen/all/data?'.http_build_query(array_replace([
            'draw' => 1, 'start' => 0, 'length' => 50,
        ], $parameters)));
    }

    #[TestWith([[]])]
    #[TestWith([['expired' => 'yes']])]
    #[TestWith([['expired' => 'no']])]
    #[TestWith([['apa_ppn' => 'yes']])]
    #[TestWith([['apa_ppn' => 'no']])]
    #[TestWith([['karyawan_id' => 2]])]
    #[TestWith([['konsumen_id' => 2]])]
    #[TestWith([['kecamatan_id' => 4]])]
    #[TestWith([['kabupaten_id' => 3]])]
    #[TestWith([['apa_ppn' => 'yes', 'karyawan_id' => 1, 'expired' => 'yes']])]
    public function test_listing_matches_existing_scope_for_every_filter(array $filters): void
    {
        $this->invoice(1, ['kas_ppn' => 1]);
        $this->invoice(2, ['konsumen_id' => 2, 'karyawan_id' => 2, 'jatuh_tempo' => '2026-11-10']);
        $this->invoice(3, ['void' => 1]);
        $this->invoice(4, ['titipan' => 1]);
        $this->invoice(5, ['lunas' => 1]);
        $this->invoice(6, ['kas_ppn' => 1, 'konsumen_id' => 2, 'karyawan_id' => 2]);
        $before = InvoiceJual::gabung($filters)->pluck('id')->sort()->values()->all();

        $response = $this->data($filters)->assertOk();

        $this->assertSame($before, collect($response->json('data'))->pluck('id')->sort()->values()->all());
        $response->assertJsonPath('recordsFiltered', count($before));
    }

    public function test_financial_totals_cover_all_rows_not_only_scroller_window_and_match_stored_amounts(): void
    {
        $this->invoice(1, ['kas_ppn' => 1, 'total' => 100000, 'diskon' => 10000, 'ppn' => 9900, 'add_fee' => 100,
            'grand_total' => 100000, 'dp' => 10000, 'dp_ppn' => 1000, 'sisa_ppn' => 5000, 'sisa_tagihan' => 70000]);
        $this->invoice(2, ['total' => 200000, 'grand_total' => 200000, 'dp' => 20000, 'sisa_tagihan' => 170000]);
        $this->invoice(3, ['void' => 1, 'grand_total' => 999999]);
        $this->installment(1, 15000, 4000);
        $this->installment(2, 10000);

        $response = $this->data(['length' => 1])->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('recordsFiltered', 2);

        $expected = ['total' => 300000, 'diskon' => 10000, 'ppn' => 9900, 'add_fee' => 100, 'grand_total' => 300000,
            'dp' => 30000, 'dp_ppn' => 1000, 'cicilan' => 29000, 'sisa_ppn' => 5000, 'sisa_tagihan' => 240000];
        $this->assertSame($expected, array_map('intval', $response->json('totals')));
        $filtered = $this->data(['search' => ['value' => 'INV-1']])->assertOk()->assertJsonPath('recordsFiltered', 1);
        $this->assertSame(19000, (int) $filtered->json('data.0.cicilan'));
        $this->assertSame(70000, (int) $filtered->json('totals.sisa_tagihan'));
        $this->assertSame('K02', $filtered->json('data.0.full_kode'));
        $filtered->assertDontSee('DO-NOT-SERIALIZE-INTERNAL-NOTE')->assertDontSee('private-identity.jpg');
        $this->data(['search' => ['value' => 'not-present']])->assertJsonPath('recordsFiltered', 0)->assertJsonPath('totals.grand_total', 0);
    }

    public function test_scroller_offsets_are_stable_and_client_column_names_cannot_change_query(): void
    {
        foreach ([3, 1, 2] as $id) {
            $this->invoice($id);
        }
        $parameters = ['length' => 1, 'columns' => [['data' => 'id', 'name' => 'DROP TABLE invoice_juals']],
            'order' => [['column' => 0, 'dir' => 'asc']]];

        $ids = [];
        foreach ([0, 1, 2] as $offset) {
            $ids[] = $this->data($parameters + ['start' => $offset])->assertOk()->json('data.0.id');
        }

        $this->assertCount(3, array_unique($ids));
        $this->assertSame($ids, array_map(fn ($offset) => $this->data($parameters + ['start' => $offset])->json('data.0.id'), [0, 1, 2]));
        $this->assertDatabaseCount('invoice_juals', 3);
    }

    #[TestWith([['length' => -1], 'length'])]
    #[TestWith([['length' => 501], 'length'])]
    #[TestWith([['start' => -1], 'start'])]
    #[TestWith([['search' => ['value' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx']], 'search.value'])]
    public function test_data_requests_are_bounded(array $parameters, string $field): void
    {
        $this->data($parameters)->assertUnprocessable()->assertJsonValidationErrors($field);
    }

    #[TestWith(['su'])]
    #[TestWith(['admin'])]
    #[TestWith(['user'])]
    public function test_existing_billing_roles_can_read_data_and_lazy_modals(string $role): void
    {
        $this->invoice(1);
        $this->actingAs(new User(['name' => 'Role uji', 'role' => $role]));

        $this->data()->assertOk();
        $this->getJson('/billing/invoice-konsumen/all/1/payment-data')->assertOk()->assertJsonPath('id', 1);
        $this->getJson('/billing/invoice-konsumen/all/1/history')->assertOk()->assertJsonPath('id', 1);
    }

    public function test_guest_cannot_read_billing_table_or_lazy_modals(): void
    {
        $this->app['auth']->forgetGuards();

        $this->data()->assertUnauthorized();
        $this->getJson('/billing/invoice-konsumen/all/1/payment-data')->assertUnauthorized();
        $this->getJson('/billing/invoice-konsumen/all/1/history')->assertUnauthorized();
    }

    public function test_sales_cannot_read_billing_table_or_lazy_modals(): void
    {
        $this->invoice(1);
        $this->actingAs(new User(['name' => 'Sales uji', 'role' => 'sales']));

        $this->data()->assertForbidden();
        $this->getJson('/billing/invoice-konsumen/all/1/payment-data')->assertForbidden();
        $this->getJson('/billing/invoice-konsumen/all/1/history')->assertForbidden();
    }

    public function test_lazy_payment_values_are_fresh_and_history_is_bounded_without_leaking_other_invoices(): void
    {
        $this->invoice(1, ['sisa_tagihan' => 75000, 'ppn_dipungut' => 1, 'kas_ppn' => 1]);
        $this->invoice(2);
        for ($i = 0; $i < 53; $i++) {
            $this->installment(1, 1000, 100);
        }
        $this->installment(2, 888888);

        $payment = $this->getJson('/billing/invoice-konsumen/all/1/payment-data')->assertOk();
        $payment->assertJsonPath('sisa_tagihan', 75000)->assertJsonPath('konsumen.nama', 'Toko Satu');
        DB::table('invoice_juals')->where('id', 1)->update(['sisa_tagihan' => 50000]);
        $this->getJson('/billing/invoice-konsumen/all/1/payment-data')->assertJsonPath('sisa_tagihan', 50000);
        $history = $this->getJson('/billing/invoice-konsumen/all/1/history')->assertOk()->assertJsonCount(50, 'data')->assertJsonPath('more', true);
        $next = $this->getJson('/billing/invoice-konsumen/all/1/history?start='.$history->json('next_start'))->assertOk()->assertJsonCount(3, 'data')->assertJsonPath('more', false);
        $this->assertCount(53, array_unique(array_merge(array_column($history->json('data'), 'id'), array_column($next->json('data'), 'id'))));
        $this->assertSame(1100, (int) $history->json('data.0.total'));
        $history->assertDontSee('888888')->assertDontSee('private-identity.jpg');
        $this->getJson('/billing/invoice-konsumen/all/999/history')->assertNotFound();
    }

    #[TestWith(['lunas'])]
    #[TestWith(['void'])]
    #[TestWith(['titipan'])]
    public function test_modals_do_not_offer_actions_on_invoices_outside_the_open_tempo_list(string $flag): void
    {
        $this->invoice(1, [$flag => 1]);

        $this->getJson('/billing/invoice-konsumen/all/1/payment-data')->assertNotFound();
        $this->getJson('/billing/invoice-konsumen/all/1/history')->assertNotFound();
    }

    public function test_index_renders_empty_table_and_shared_lazy_modals_without_loading_invoice_models(): void
    {
        Schema::create('jabatans', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->boolean('is_sales')->default(false);
        });
        Schema::table('karyawans', function (Blueprint $table): void {
            $table->integer('jabatan_id')->nullable();
        });
        Schema::create('pajaks', function (Blueprint $table): void {
            $table->id();
            $table->string('untuk');
            $table->integer('persen');
        });
        DB::table('pajaks')->insert(['untuk' => 'ppn', 'persen' => 11]);
        $this->invoice(1);
        $this->installment(1, 1000, 100);
        DB::enableQueryLog();
        DB::flushQueryLog();

        $response = $this->get('/billing/invoice-konsumen/all')->assertOk()->assertViewMissing('data')
            ->assertSee('customer-invoice-table.js')->assertSee('customerInvoiceConfig')->assertSee('<tbody></tbody>', false);

        $queries = collect(DB::getQueryLog())->pluck('query')->all();
        DB::disableQueryLog();
        $this->assertSame([], array_values(array_filter($queries, fn ($sql): bool => str_contains($sql, 'invoice_juals') || str_contains($sql, 'invoice_jual_cicils'))));
        $html = $response->getContent();
        foreach (['cicilanModal', 'cicilanNonModal', 'invoice-history-modal'] as $id) {
            $this->assertSame(1, substr_count($html, 'id="'.$id.'"'));
        }
        $response->assertDontSee('INV-1')->assertDontSee('modalHistoriCicilan1')->assertDontSee('DO-NOT-SERIALIZE-INTERNAL-NOTE');
    }

    public function test_zero_value_installment_still_has_history_count(): void
    {
        $this->invoice(1);
        $this->installment(1, 0, 0);

        $this->data()->assertOk()->assertJsonPath('data.0.cicilan', 0)->assertJsonPath('data.0.cicilan_count', 1);
        $this->getJson('/billing/invoice-konsumen/all/1/history')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.total', 0);
    }

    public function test_smart_search_matches_cross_column_terms_quoted_phrases_and_formatted_money(): void
    {
        $this->invoice(1, ['sisa_tagihan' => 100000]);
        $this->invoice(2, ['konsumen_id' => 2, 'karyawan_id' => 2, 'total' => 250000, 'grand_total' => 250000, 'sisa_tagihan' => 250000]);
        DB::table('konsumens')->where('id', 2)->update(['nama' => 'Alpha Toko', 'plafon' => 250000]);
        DB::table('konsumens')->where('id', 1)->update(['plafon' => 100000]);

        $this->data(['search' => ['value' => 'Alpha Satu']])->assertOk()->assertJsonPath('recordsFiltered', 1)->assertJsonPath('data.0.id', 1);
        $this->data(['search' => ['value' => '"Toko Satu"']])->assertOk()->assertJsonPath('recordsFiltered', 1)->assertJsonPath('data.0.id', 1);
        $this->data(['search' => ['value' => '"Toko Alpha"']])->assertOk()->assertJsonPath('recordsFiltered', 0);
        $this->data(['search' => ['value' => '100.000']])->assertOk()->assertJsonPath('recordsFiltered', 1)->assertJsonPath('data.0.id', 1);
    }

    public function test_numeric_search_respects_displayed_thousands_and_accepts_exact_unformatted_amount(): void
    {
        $this->invoice(1, ['total' => 11000, 'grand_total' => 11000, 'sisa_tagihan' => 11000]);
        $this->invoice(2, ['total' => 100000, 'grand_total' => 100000, 'sisa_tagihan' => 100000]);
        $this->data(['search' => ['value' => '100']])->assertOk()->assertJsonPath('recordsFiltered', 1)->assertJsonPath('data.0.id', 2);
        $this->data(['search' => ['value' => '100000']])->assertOk()->assertJsonPath('recordsFiltered', 1)->assertJsonPath('data.0.id', 2);
    }

    public function test_unloaded_installment_accessor_uses_one_aggregate_query_and_keeps_legacy_amounts(): void
    {
        $this->invoice(1);
        $this->installment(1, 10000, 1100);
        $this->installment(1, 5000, 500);
        $invoice = InvoiceJual::findOrFail(1);
        DB::enableQueryLog();
        DB::flushQueryLog();

        $this->assertSame(16600, (int) $invoice->raw_total_cicilan);
        $this->assertCount(1, DB::getQueryLog());
        DB::flushQueryLog();
        $this->assertSame('16.600', $invoice->total_cicilan);
        $this->assertCount(1, DB::getQueryLog());
        DB::disableQueryLog();
    }

    public function test_allowed_financial_sort_is_applied_and_customer_search_uses_literal_wildcards(): void
    {
        $this->invoice(1, ['sisa_tagihan' => 1000]);
        $this->invoice(2, ['sisa_tagihan' => 5000]);
        DB::table('konsumens')->where('id', 1)->update(['nama' => 'Toko 100%_Asli']);

        $this->data(['order' => [['column' => 13, 'dir' => 'desc']]])->assertOk()->assertJsonPath('data.0.id', 2);
        $this->data(['search' => ['value' => '%_']])->assertOk()->assertJsonPath('recordsFiltered', 2);
        $this->data(['search' => ['value' => '%_Palsu']])->assertOk()->assertJsonPath('recordsFiltered', 0);
        $this->data(['search' => ['value' => 'K02']])->assertOk()->assertJsonPath('recordsFiltered', 2);
        $this->data(['search' => ['value' => 'K0']])->assertOk()->assertJsonPath('recordsFiltered', 2);
    }

    public function test_payment_modal_handles_missing_optional_store_code_and_does_not_return_unrelated_customer_fields(): void
    {
        $this->invoice(1);
        DB::table('konsumens')->where('id', 1)->update(['kode_toko_id' => null]);

        $this->getJson('/billing/invoice-konsumen/all/1/payment-data')->assertOk()
            ->assertJsonPath('konsumen.kode_toko.kode', '')->assertJsonPath('konsumen.nama', 'Toko Satu')
            ->assertJsonMissingPath('konsumen.upload_ktp')->assertJsonMissingPath('internal_note');
    }

    public function test_query_count_does_not_grow_with_number_of_invoices_and_loaded_accessor_avoids_n_plus_one(): void
    {
        $this->invoice(1);
        $this->installment(1, 10000, 1100);
        DB::enableQueryLog();
        DB::flushQueryLog();
        $this->data()->assertOk();
        $smallCount = count(DB::getQueryLog());
        for ($i = 2; $i <= 80; $i++) {
            $this->invoice($i);
            $this->installment($i, 1000, 100);
        }
        DB::flushQueryLog();

        $this->data()->assertOk()->assertJsonCount(50, 'data')->assertJsonPath('recordsFiltered', 80);
        $largeCount = count(DB::getQueryLog());
        $this->assertSame($smallCount, $largeCount);
        $this->assertLessThanOrEqual(8, $largeCount);
        $invoices = InvoiceJual::with('invoice_jual_cicil')->get();
        DB::flushQueryLog();
        $serialized = $invoices->toArray();
        $this->assertSame('11.100', $serialized[0]['total_cicilan']);
        $this->assertCount(0, DB::getQueryLog());
        DB::disableQueryLog();
    }
}
