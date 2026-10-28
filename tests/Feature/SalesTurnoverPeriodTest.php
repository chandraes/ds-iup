<?php

namespace Tests\Feature;

use App\Models\transaksi\InvoiceJual;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class SalesTurnoverPeriodTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['database.default' => 'turnover_test', 'database.connections.turnover_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        DB::connection()->getPdo()->sqliteCreateFunction('YEAR', fn (?string $date): ?int => $date ? (int) substr($date, 0, 4) : null);
        Schema::create('karyawans', function (Blueprint $table): void {
            $table->id();
            $table->integer('jabatan_id');
            $table->boolean('status');
            $table->string('nama');
        });
        Schema::create('invoice_juals', function (Blueprint $table): void {
            $table->id();
            $table->integer('karyawan_id')->nullable();
            $table->boolean('titipan')->default(false);
            $table->boolean('void')->default(false);
            $table->boolean('lunas')->default(false);
            $table->bigInteger('grand_total');
            $table->timestamps();
        });
        DB::table('karyawans')->insert(['id' => 1, 'jabatan_id' => 3, 'status' => 1, 'nama' => 'Sales Uji']);
        $user = new User(['name' => 'Admin Uji', 'role' => 'admin']);
        $user->id = 999;
        $this->actingAs($user);
        $this->travelTo(Carbon::parse('2026-10-24 15:30:00'));
    }

    private function invoice(string $date, int $amount, array $overrides = []): void
    {
        DB::table('invoice_juals')->insert(array_replace(['karyawan_id' => 1, 'grand_total' => $amount, 'created_at' => $date, 'updated_at' => $date], $overrides));
    }

    public function test_period_includes_26_through_25_and_void_adjustments(): void
    {
        $this->invoice('2026-09-25 23:59:59', 900);
        $this->invoice('2026-09-26 00:00:00', 100);
        $this->invoice('2026-10-25 23:59:59', 200);
        $this->invoice('2026-10-26 00:00:00', 800);
        $this->invoice('2026-08-10 12:00:00', 50, ['void' => 1, 'updated_at' => '2026-10-25 23:59:59']);
        $this->invoice('2026-09-28 10:00:00', 700, ['titipan' => 1]);
        $response = $this->get(route('statistik.omset-harian-sales', ['month' => 10, 'year' => 2026]));
        $response->assertOk()->assertSee('26 September 2026')->assertSee('25 Oktober 2026')
            ->assertSee(route('statistik.omset-harian-sales.detail', ['tanggal' => '2026-09-26', 'karyawan_id' => 1, 'month' => 10, 'year' => 2026]))
            ->assertViewHas('rows', fn ($rows): bool => count($rows) === 30 && $rows[0]['tanggal'] === '2026-09-26' && $rows[0][1] === 100 && $rows[29]['tanggal'] === '2026-10-25' && $rows[29][1] === 150)
            ->assertViewHas('totals', fn ($totals): bool => $totals[1] === 250);
        $calendar = (new InvoiceJual)->omset_harian(10, 2026);
        $this->assertSame('2026-10-01', $calendar['data'][0]['tanggal']);
        $this->assertCount(31, $calendar['data']);
    }

    #[TestWith([1, 2027, '2026-12-26', '2027-01-25', 31])]
    #[TestWith([3, 2024, '2024-02-26', '2024-03-25', 29])]
    #[TestWith([3, 2026, '2026-02-26', '2026-03-25', 28])]
    public function test_period_handles_year_rollover_and_leap_years(int $month, int $year, string $first, string $last, int $days): void
    {
        $this->get(route('statistik.omset-harian-sales', compact('month', 'year')))->assertOk()
            ->assertViewHas('rows', fn ($rows): bool => count($rows) === $days && $rows[0]['tanggal'] === $first && $rows[$days - 1]['tanggal'] === $last)
            ->assertViewHas('years', fn ($years): bool => $years->contains($year));
    }

    public function test_paid_mode_uses_payment_date_and_excludes_unpaid_void_and_deposit_invoices(): void
    {
        $this->invoice('2026-08-10 12:00:00', 100, ['lunas' => 1, 'updated_at' => '2026-09-26 00:00:00']);
        $this->invoice('2026-09-20 12:00:00', 200, ['lunas' => 1, 'updated_at' => '2026-10-25 23:59:59']);
        $this->invoice('2026-09-28 12:00:00', 800, ['lunas' => 1, 'updated_at' => '2026-10-26 00:00:00']);
        $this->invoice('2026-09-28 12:00:00', 900, ['lunas' => 1, 'updated_at' => '2026-09-25 23:59:59']);
        $this->invoice('2026-10-01 12:00:00', 300, ['lunas' => 0]);
        $this->invoice('2026-10-02 12:00:00', 400, ['lunas' => 1, 'void' => 1]);
        $this->invoice('2026-10-03 12:00:00', 500, ['lunas' => 1, 'titipan' => 1]);
        $response = $this->get(route('statistik.omset-harian-sales', ['month' => 10, 'year' => 2026, 'mode' => 'paid']));
        $response->assertOk()->assertViewHas('selectedMode', 'paid')
            ->assertViewHas('totals', fn ($totals): bool => $totals[1] === 300)
            ->assertViewHas('rows', fn ($rows): bool => $rows[0][1] === 100 && $rows[29][1] === 200)
            ->assertSee(route('statistik.omset-harian-sales.detail', ['tanggal' => '2026-09-26', 'karyawan_id' => 1, 'month' => 10, 'year' => 2026, 'mode' => 'paid']));
        $default = $this->get(route('statistik.omset-harian-sales', ['month' => 10, 'year' => 2026]));
        $default->assertViewHas('selectedMode', 'all')->assertViewHas('totals', fn ($totals): bool => $totals[1] === 2000);
    }

    public function test_paid_mode_detail_matches_aggregate_and_preserves_mode_on_back_link(): void
    {
        Schema::table('invoice_juals', function (Blueprint $table): void {
            $table->unsignedBigInteger('konsumen_id')->nullable();
            $table->string('kode')->nullable();
        });
        Schema::create('invoice_jual_details', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('invoice_jual_id');
        });
        Schema::create('konsumens', function (Blueprint $table): void {
            $table->id();
        });
        Schema::create('kode_tokos', function (Blueprint $table): void {
            $table->id();
        });
        Schema::create('wilayahs', function (Blueprint $table): void {
            $table->id();
        });
        $this->invoice('2026-08-01 00:00:00', 100, ['lunas' => 1, 'updated_at' => '2026-10-01 12:00:00', 'kode' => 'LUNAS-1']);
        $this->invoice('2026-10-01 12:00:00', 200, ['lunas' => 0, 'kode' => 'BELUM-1']);
        $this->invoice('2026-10-01 12:00:00', 300, ['lunas' => 1, 'void' => 1, 'kode' => 'VOID-1']);
        $response = $this->get(route('statistik.omset-harian-sales.detail', ['tanggal' => '2026-10-01', 'karyawan_id' => 1, 'mode' => 'paid', 'month' => 10, 'year' => 2026]));
        $response->assertOk()->assertSee('LUNAS-1')->assertDontSee('BELUM-1')->assertDontSee('VOID-1')
            ->assertViewHas('data', fn ($data): bool => $data['data']->sum('grand_total') === 100 && $data['void']->isEmpty())
            ->assertSee(route('statistik.omset-harian-sales', ['month' => 10, 'year' => 2026, 'mode' => 'paid']));
        $sales = new User(['name' => 'Sales Uji', 'role' => 'sales', 'karyawan_id' => 1]);
        $sales->id = 22;
        $this->actingAs($sales);
        $salesResponse = $this->get(route('sales.omset-harian.detail', ['tanggal' => '2026-10-01', 'karyawan_id' => 1, 'mode' => 'paid', 'month' => 10, 'year' => 2026]));
        $salesResponse->assertOk()->assertViewIs('statistik.omset-harian.detail')->assertSee('LUNAS-1')->assertDontSee('BELUM-1')
            ->assertSee(route('sales.omset-harian', ['month' => 10, 'year' => 2026, 'mode' => 'paid']))
            ->assertSee(route('sales.omset-harian.detail.invoice', ['invoice' => 1]));

    }

    public function test_sales_uses_shared_view_and_only_own_turnover_even_with_tampered_filter(): void
    {
        DB::table('karyawans')->insert(['id' => 2, 'jabatan_id' => 3, 'status' => 1, 'nama' => 'Sales Lain']);
        $this->invoice('2026-08-01', 100, ['lunas' => 1, 'updated_at' => '2026-10-01']);
        $this->invoice('2025-10-01', 999, ['karyawan_id' => 2, 'lunas' => 1, 'updated_at' => '2026-10-01']);
        $user = new User(['name' => 'Sales', 'role' => 'sales', 'karyawan_id' => 1]);
        $user->id = 22;
        $this->actingAs($user);
        $response = $this->get(route('sales.omset-harian', ['month' => 10, 'year' => 2026, 'mode' => 'paid', 'karyawan_id' => 2]));
        $response->assertOk()->assertViewIs('statistik.omset-harian.index')->assertViewHas('selectedMode', 'paid')
            ->assertViewHas('karyawans', fn ($people): bool => $people->modelKeys() === [1])
            ->assertViewHas('totals', fn ($totals): bool => $totals->all() === [1 => 100])
            ->assertViewHas('years', fn ($years): bool => ! $years->contains(2025))
            ->assertSee(route('sales.omset-harian.detail', ['tanggal' => '2026-10-01', 'karyawan_id' => 1, 'month' => 10, 'year' => 2026, 'mode' => 'paid']))
            ->assertDontSee('Sales Lain')->assertDontSee('Kembali ke statistik');
        $this->getJson(route('sales.omset-harian.detail', ['tanggal' => '2026-10-01', 'karyawan_id' => 2]))->assertForbidden();
        $invoice = InvoiceJual::where('karyawan_id', 2)->first();
        $this->get(route('sales.omset-harian.detail.invoice', $invoice))->assertForbidden();
        $this->get(route('statistik.omset-harian-sales'))->assertForbidden();
    }

    public function test_sales_without_employee_cannot_open_turnover_or_detail(): void
    {
        $user = new User(['name' => 'Sales', 'role' => 'sales']);
        $user->id = 22;
        $this->actingAs($user);
        $this->get(route('sales.omset-harian'))->assertForbidden();
        $this->getJson(route('sales.omset-harian.detail', ['tanggal' => '2026-10-01', 'karyawan_id' => 1]))->assertForbidden();
    }

    public function test_invalid_turnover_mode_is_rejected_on_index_and_detail(): void
    {
        $this->getJson(route('statistik.omset-harian-sales', ['mode' => 'other']))->assertUnprocessable()->assertJsonValidationErrors('mode');
        $this->getJson(route('statistik.omset-harian-sales.detail', ['tanggal' => '2026-10-01', 'karyawan_id' => 1, 'mode' => 'other']))->assertUnprocessable()->assertJsonValidationErrors('mode');
    }

    public function test_invalid_period_filters_are_rejected(): void
    {
        $this->getJson(route('statistik.omset-harian-sales', ['month' => 13, 'year' => 0]))->assertUnprocessable()->assertJsonValidationErrors(['month', 'year']);
    }

    public function test_empty_sales_list_displays_a_clear_empty_state(): void
    {
        DB::table('karyawans')->update(['status' => 0]);
        $this->get(route('statistik.omset-harian-sales'))->assertOk()->assertSee('Belum ada sales aktif')
            ->assertViewHas('totals', fn ($totals): bool => $totals->sum() === 0);
    }
}
