<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FormLainReportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['database.default' => 'lain_report_test', 'database.connections.lain_report_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        Schema::create('kas_besars', function (Blueprint $table): void {
            $table->id();
            $table->boolean('lain_lain');
            $table->boolean('ppn_kas');
            $table->boolean('jenis');
            $table->bigInteger('nominal');
            foreach (['uraian', 'nama_rek', 'no_rek', 'bank'] as $field) {
                $table->string($field)->nullable();
            }
            $table->timestamps();
        });
        $this->travelTo(Carbon::parse('2026-10-24 10:00:00'));
        $user = new User(['name' => 'Admin Uji', 'role' => 'admin']);
        $user->id = 999;
        $this->actingAs($user);
    }

    private function transaction(array $overrides = []): void
    {
        DB::table('kas_besars')->insert(array_replace(['lain_lain' => 1, 'ppn_kas' => 1, 'jenis' => 1, 'nominal' => 100000,
            'uraian' => 'Transaksi uji', 'nama_rek' => 'Penerima Uji', 'no_rek' => '00123', 'bank' => 'Bank Uji',
            'created_at' => '2026-10-20 12:00:00', 'updated_at' => '2026-10-20 12:00:00'], $overrides));
    }

    public function test_cash_selection_is_required_before_report_and_invalid_filters_are_rejected(): void
    {
        $this->get(route('rekap.lain-lain'))->assertOk()->assertViewIs('rekap.lain-lain-select');
        foreach (['ppn_kas' => 2, 'bulan' => 13, 'tahun' => 0, 'search' => str_repeat('a', 101)] as $field => $value) {
            $this->getJson(route('rekap.lain-lain', [$field => $value]))->assertUnprocessable()->assertJsonValidationErrors($field);
        }
        $this->app['auth']->forgetGuards();
        $this->get(route('rekap.lain-lain'))->assertRedirect(route('login'));
    }

    public function test_report_separates_cash_types_dates_and_other_transactions(): void
    {
        $this->transaction(['uraian' => 'Masuk PPN']);
        $this->transaction(['uraian' => 'Keluar PPN', 'jenis' => 0, 'nominal' => 40000]);
        $this->transaction(['uraian' => 'Non PPN', 'ppn_kas' => 0, 'nominal' => 70000]);
        $this->transaction(['uraian' => 'Bukan lain-lain', 'lain_lain' => 0]);
        $this->transaction(['uraian' => 'Bulan lain', 'created_at' => '2026-11-01 00:00:00']);
        $this->get(route('rekap.lain-lain', ['ppn_kas' => 1]))->assertOk()->assertViewIs('rekap.lain-lain');
        $this->getJson(route('rekap.lain-lain', ['ppn_kas' => 1, 'draw' => 1]), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertJsonPath('recordsTotal', 2)->assertJsonPath('recordsFiltered', 2)
            ->assertJsonPath('totals.masuk', 100000)->assertJsonPath('totals.keluar', 40000)
            ->assertJsonPath('data.0.uraian', 'Keluar PPN')->assertJsonPath('data.0.keluar', 40000)
            ->assertJsonPath('data.1.masuk', 100000);
        $this->getJson(route('rekap.lain-lain', ['ppn_kas' => 0]), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertJsonPath('recordsTotal', 1)->assertJsonPath('data.0.uraian', 'Non PPN')
            ->assertJsonPath('totals.masuk', 70000);
    }

    public function test_search_and_totals_cover_all_pages_and_escape_transaction_text(): void
    {
        for ($i = 0; $i < 26; $i++) {
            $this->transaction(['uraian' => '<script>alert(1)</script>', 'nominal' => 1000]);
        }
        $this->transaction(['nama_rek' => 'Penerima lain', 'bank' => 'Bank lain', 'no_rek' => '999']);
        $parameters = ['ppn_kas' => 1, 'search' => ['value' => '00123'], 'start' => 0, 'length' => 25, 'draw' => 7];
        $headers = ['X-Requested-With' => 'XMLHttpRequest'];
        $this->getJson(route('rekap.lain-lain', $parameters), $headers)->assertOk()
            ->assertJsonPath('draw', 7)->assertJsonPath('recordsTotal', 27)->assertJsonPath('recordsFiltered', 26)
            ->assertJsonCount(25, 'data')->assertJsonPath('totals.masuk', 26000)
            ->assertJsonPath('data.0.uraian', '<script>alert(1)</script>');
        $parameters['start'] = 25;
        $this->getJson(route('rekap.lain-lain', $parameters), $headers)->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('totals.masuk', 26000);
        $parameters['start'] = 0;
        $parameters['search']['value'] = 'Tidak ditemukan';
        $this->getJson(route('rekap.lain-lain', $parameters), $headers)->assertOk()
            ->assertJsonCount(0, 'data')->assertJsonPath('recordsFiltered', 0)->assertJsonPath('totals.count', 0);
        $parameters['search']['value'] = str_repeat('a', 101);
        $this->getJson(route('rekap.lain-lain', $parameters), $headers)->assertUnprocessable()->assertJsonValidationErrors('search.value');
    }

    public function test_scrolling_fetches_bounded_batches_without_changing_totals(): void
    {
        for ($i = 0; $i < 125; $i++) {
            $this->transaction(['uraian' => 'Transaksi '.$i, 'nominal' => 1000]);
        }
        $headers = ['X-Requested-With' => 'XMLHttpRequest'];
        $parameters = ['ppn_kas' => 1, 'length' => 1000, 'start' => 0, 'draw' => 1];
        $this->getJson(route('rekap.lain-lain', $parameters), $headers)->assertOk()
            ->assertJsonCount(100, 'data')->assertJsonPath('recordsFiltered', 125)
            ->assertJsonPath('data.0.uraian', 'Transaksi 124')->assertJsonPath('totals.masuk', 125000);
        $parameters['start'] = 100;
        $parameters['draw'] = 2;
        $this->getJson(route('rekap.lain-lain', $parameters), $headers)->assertOk()
            ->assertJsonCount(25, 'data')->assertJsonPath('draw', 2)
            ->assertJsonPath('data.0.uraian', 'Transaksi 24')->assertJsonPath('totals.masuk', 125000);
        $parameters['length'] = -1;
        $parameters['start'] = 0;
        $this->getJson(route('rekap.lain-lain', $parameters), $headers)->assertOk()->assertJsonCount(1, 'data');
    }
}
