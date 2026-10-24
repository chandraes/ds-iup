<?php

namespace Tests\Feature;

use App\Models\db\Jabatan;
use App\Models\db\Karyawan;
use App\Models\KasBesar;
use App\Models\Kasbon;
use App\Models\PasswordKonfirmasi;
use App\Models\RekapGaji;
use App\Models\RekapGajiDetail;
use App\Models\Rekening;
use App\Models\User;
use App\Services\EmployeePayrollService;
use App\Services\KasbonMessage;
use App\Services\KasbonService;
use Carbon\Carbon;
use Database\Factories\KaryawanFactory;
use Database\Factories\KasbonFactory;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class KasbonTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['attendance.enabled' => false, 'database.default' => 'kasbon_test', 'database.connections.kasbon_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
        ]]);
        $this->travelTo(Carbon::parse('2026-10-20 10:00:00'));
        foreach (['2024_06_09_212028_create_jabatans_table.php', '2024_06_09_212103_create_karyawans_table.php', '2024_06_30_154442_add_column_to_karyawans_table.php',
            '2026_10_23_072658_add_perjanjian_kerja_to_karyawans_table.php', '2026_10_23_074221_add_sales_compensation_to_karyawans_table.php',
            '2024_06_11_213959_create_rekap_gajis_table.php', '2024_06_11_214012_create_rekap_gaji_details_table.php'] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
        Schema::create('kas_besars', function (Blueprint $table): void {
            $table->id();
            $table->boolean('ppn_kas');
            $table->boolean('jenis');
            $table->string('uraian');
            $table->bigInteger('nominal');
            $table->bigInteger('saldo');
            $table->bigInteger('modal_investor_terakhir');
            foreach (['bank', 'no_rek', 'nama_rek'] as $field) {
                $table->string($field);
            }
            $table->timestamps();
        });
        Schema::create('rekenings', function (Blueprint $table): void {
            $table->id();
            foreach (['untuk', 'bank', 'no_rek', 'nama_rek'] as $field) {
                $table->string($field);
            }
            $table->timestamps();
        });
        Schema::create('group_was', function (Blueprint $table): void {
            $table->id();
            $table->string('untuk');
            $table->string('nama_group');
        });
        Schema::create('password_konfirmasis', function (Blueprint $table): void {
            $table->id();
            $table->string('password');
            $table->timestamps();
        });
        (require database_path('migrations/2026_10_24_095600_create_kasbons_table.php'))->up();
        (require database_path('migrations/2026_10_24_095601_add_kasbon_deductions_to_rekap_gaji_details_table.php'))->up();
        (require database_path('migrations/2026_10_24_100217_enforce_unique_payroll_period_on_rekap_gajis_table.php'))->up();
        $user = new User(['name' => 'Admin uji', 'role' => 'admin']);
        $user->id = 999;
        $this->actingAs($user);
    }

    private function fund(int $amount = 20000000): KasBesar
    {
        return KasBesar::create(['ppn_kas' => 1, 'jenis' => 1, 'uraian' => 'Saldo awal', 'nominal' => $amount,
            'saldo' => $amount, 'modal_investor_terakhir' => 123000, 'bank' => '-', 'nama_rek' => '-', 'no_rek' => '-']);
    }

    #[TestWith(['potong_gaji', false])]
    #[TestWith(['cicilan', false])]
    #[TestWith(['cicilan', true])]
    public function test_whatsapp_message_matches_saind_format_using_iup_transaction_data(string $type, bool $returned): void
    {
        $employee = KaryawanFactory::new()->create(['nama' => 'Pegawai Uji']);
        $loan = KasbonFactory::new()->create(['karyawan_id' => $employee->id, 'jenis' => $type,
            'cicil_kali' => 3, 'mulai_periode' => '2026-11-01']);
        $cash = $this->fund();
        $cash->forceFill(['nominal' => 600000, 'saldo' => 19400000, 'uraian' => 'Pengembalian kasbon #'.$loan->id,
            'bank' => 'Bank Uji', 'nama_rek' => 'Penerima Uji', 'no_rek' => '001234']);

        $message = app(KasbonMessage::class)->format($loan, $cash, $returned);

        $this->assertStringStartsWith($returned ? "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n*Form Void Kasbon Staff*" : "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n*Form Kasbon Staff*", $message);
        $this->assertStringContainsString("Nama : Pegawai Uji\n", $message);
        $this->assertStringContainsString('Uraian : '.($returned ? $cash->uraian : ($type === 'cicilan' ? 'Cicilan 3X' : 'Potong Gaji')), $message);
        if ($type === 'cicilan' && ! $returned) {
            $this->assertStringContainsString('Mulai : November 2026', $message);
        } else {
            $this->assertStringNotContainsString('Mulai :', $message);
        }
        $this->assertStringContainsString('Nilai :  *Rp. 600.000*', $message);
        $this->assertStringContainsString("Ditransfer ke rek:\n\nBank     : Bank Uji\nNama    : Penerima Uji\nNo. Rek : 001234", $message);
        $this->assertStringContainsString("Sisa Saldo Kas Besar PPN : \nRp. 19.400.000", $message);
        $this->assertStringContainsString("Total Modal Investor PPN : \nRp. 123.000", $message);
        $this->assertStringEndsWith("Terima kasih 🙏🙏🙏\n", $message);
    }

    /** @return array<string, int|string> */
    private function payload(Karyawan $employee, array $overrides = []): array
    {
        return array_replace(['karyawan_id' => $employee->id, 'jenis' => 'potong_gaji', 'nominal' => 1000000,
            'submission_token' => (string) Str::uuid()], $overrides);
    }

    public function test_billing_offers_forms_and_report_lives_under_rekap(): void
    {
        $this->get(route('billing.kasbon.index'))->assertOk()->assertViewIs('billing.kasbon.index')
            ->assertSee(route('billing.kasbon.create', ['jenis' => 'potong_gaji']))
            ->assertSee(route('billing.kasbon.create', ['jenis' => 'cicilan']))->assertDontSee('Cetak rekap');
        $this->get(route('rekap.kasbon'))->assertOk()->assertViewIs('rekap.kasbon.index')->assertSee('Cetak rekap');
        $this->assertStringContainsString('/rekap/kasbon', route('rekap.kasbon'));
        $user = new User(['name' => 'User', 'role' => 'user']);
        $user->id = 999;
        $this->actingAs($user)->get(route('billing.kasbon.index'))->assertOk()->assertDontSee('Isi form cicilan');
        $user->role = 'investor';
        $this->actingAs($user)->get(route('rekap.kasbon'))->assertForbidden();
    }

    public function test_report_tabs_have_independent_defaults_filters_and_print_totals(): void
    {
        $employee = KaryawanFactory::new()->create();
        KasbonFactory::new()->create(['karyawan_id' => $employee->id]);
        KasbonFactory::new()->create(['karyawan_id' => $employee->id, 'lunas' => true, 'sisa_kas' => 0, 'total_bayar' => 100000]);
        KasbonFactory::new()->installment()->create(['karyawan_id' => $employee->id, 'tanggal' => '2026-09-01']);
        KasbonFactory::new()->installment()->create(['karyawan_id' => $employee->id, 'lunas' => true, 'sisa_kas' => 0, 'total_bayar' => 900000]);
        $this->get(route('rekap.kasbon'))->assertOk()->assertSee('Rekap kasbon potong gaji')->assertSee('filter-month')->assertViewHas('loans', fn ($loans) => $loans->total() === 2)->assertViewHas('summary', fn ($summary) => (int) $summary->nominal === 200000);
        $this->get(route('rekap.kasbon', ['jenis' => 'cicilan', 'bulan' => 10]))->assertOk()->assertSee('Rekap kasbon cicilan')->assertDontSee('filter-month')->assertSee('Jadwal angsuran')->assertViewHas('loans', fn ($loans) => $loans->total() === 1)->assertViewHas('summary', fn ($summary) => (int) $summary->sisa === 900000);
        $this->get(route('rekap.kasbon', ['jenis' => 'cicilan', 'status' => 'lunas', 'print' => 1]))->assertOk()->assertSee('Cetak rekap cicilan')->assertViewHas('loans', fn ($loans) => $loans->total() === 1)->assertViewHas('summary', fn ($summary) => (int) $summary->dibayar === 900000);
    }

    public function test_disbursement_uses_iup_columns_and_is_idempotent(): void
    {
        $employee = KaryawanFactory::new()->create();
        $this->fund();
        $payload = $this->payload($employee, ['nominal' => '1.000.000']);
        $this->post(route('billing.kasbon.store'), $payload)->assertRedirect(route('billing.kasbon.index'));
        $this->post(route('billing.kasbon.store'), $payload)->assertRedirect(route('billing.kasbon.index'));
        $this->assertSame(1, Kasbon::count());
        $cash = KasBesar::latest('id')->first();
        $this->assertEquals(19000000, $cash->saldo);
        $this->assertEquals(0, $cash->jenis);
        $this->assertEquals(1, $cash->ppn_kas);
        $this->assertSame($employee->no_rek, $cash->no_rek);
        $this->assertEquals(123000, $cash->modal_investor_terakhir);
        $this->get(route('rekap.kasbon'))->assertOk()->assertSee($employee->nama);
        $this->get(route('billing.kasbon.create'))->assertOk();
    }

    #[TestWith(['potong_gaji', 2000000, true])]
    #[TestWith(['potong_gaji', 2000001, false])]
    #[TestWith(['cicilan', 2400000, true])]
    #[TestWith(['cicilan', 2400001, false])]
    public function test_income_limits_are_enforced(string $type, int $amount, bool $accepted): void
    {
        $employee = KaryawanFactory::new()->create();
        $this->fund();
        $payload = $this->payload($employee, ['jenis' => $type, 'nominal' => $amount, 'cicil_kali' => 2, 'mulai_bulan' => 10, 'mulai_tahun' => 2026]);
        $response = $this->post(route('billing.kasbon.store'), $payload);
        if ($accepted) {
            $response->assertSessionHasNoErrors();
            $this->assertSame(1, Kasbon::count());
        } else {
            $response->assertSessionHasErrors('nominal');
            $this->assertSame(0, Kasbon::count());
            $this->assertSame(1, KasBesar::count());
        }
    }

    public function test_summary_offsets_both_types_and_respects_start_period(): void
    {
        $employee = KaryawanFactory::new()->create();
        KasbonFactory::new()->create(['karyawan_id' => $employee->id, 'nominal' => 500000, 'sisa_kas' => 500000]);
        KasbonFactory::new()->installment()->create(['karyawan_id' => $employee->id, 'mulai_periode' => '2026-11-01']);
        $service = app(KasbonService::class);
        $october = $service->summary($employee, now());
        $november = $service->summary($employee, now()->addMonth());
        $this->assertSame(1500000, $october['ordinary_available']);
        $this->assertSame(1350000, $november['ordinary_available']);
        $this->assertSame(1050000, $october['installment_limit']);
        $this->getJson(route('billing.kasbon.summary', ['karyawan_id' => $employee->id, 'bulan' => 11, 'tahun' => 2026]))->assertOk()->assertJsonPath('installment_due', 300000);
    }

    public function test_date_active_sales_and_roles_are_checked_on_the_server(): void
    {
        $this->fund();
        $employee = KaryawanFactory::new()->create();
        $this->travelTo(Carbon::parse('2026-10-15'));
        $this->post(route('billing.kasbon.store'), $this->payload($employee))->assertSessionHasErrors('nominal');
        $this->travelTo(Carbon::parse('2026-10-16'));
        $employee->update(['status' => 0]);
        $this->post(route('billing.kasbon.store'), $this->payload($employee))->assertSessionHasErrors('karyawan_id');
        $employee->update(['status' => 1, 'jabatan_id' => Jabatan::create(['nama' => 'SALES'])->id]);
        $this->post(route('billing.kasbon.store'), $this->payload($employee))->assertSessionHasErrors('karyawan_id');
        $user = new User(['name' => 'User', 'role' => 'user']);
        $user->id = 999;
        $this->actingAs($user)->post(route('billing.kasbon.store'), $this->payload($employee, ['jenis' => 'cicilan', 'cicil_kali' => 2, 'mulai_bulan' => 11, 'mulai_tahun' => 2026]))->assertForbidden();
        $user->role = 'sales';
        $this->actingAs($user)->get(route('rekap.kasbon'))->assertForbidden();
        $this->assertSame(0, Kasbon::count());
    }

    public function test_second_active_installment_and_invalid_money_are_rejected(): void
    {
        $employee = KaryawanFactory::new()->create();
        $this->fund();
        $payload = $this->payload($employee, ['jenis' => 'cicilan', 'cicil_kali' => 3, 'mulai_bulan' => 11, 'mulai_tahun' => 2026]);
        $this->post(route('billing.kasbon.store'), $payload)->assertSessionHasNoErrors();
        $payload['submission_token'] = (string) Str::uuid();
        $this->post(route('billing.kasbon.store'), $payload)->assertSessionHasErrors('karyawan_id');
        foreach (['-1', '0', '10.50', 'abc'] as $invalid) {
            $this->post(route('billing.kasbon.store'), $this->payload($employee, ['nominal' => $invalid]))->assertSessionHasErrors('nominal');
        }
        $this->assertSame(1, Kasbon::count());
    }

    public function test_cancellation_returns_only_remaining_balance_and_cannot_repeat(): void
    {
        $employee = KaryawanFactory::new()->create();
        $loan = KasbonFactory::new()->installment()->create(['karyawan_id' => $employee->id, 'total_bayar' => 300000, 'sisa_kas' => 600000]);
        Rekening::create(['untuk' => 'kas-besar-ppn', 'bank' => 'Bank perusahaan', 'no_rek' => '000111', 'nama_rek' => 'IUP']);
        PasswordKonfirmasi::create(['password' => 'confirm-test']);
        $url = route('rekap.kasbon.cancel', $loan);
        $this->post($url, ['alasan' => 'Dana dikembalikan', 'password' => 'wrong'])->assertSessionHasErrors('password');
        $this->post($url, ['alasan' => 'Dana dikembalikan', 'password' => 'confirm-test'])->assertRedirect(route('rekap.kasbon'));
        $this->assertTrue($loan->fresh()->void);
        $this->assertEquals(300000, $loan->fresh()->total_bayar);
        $this->assertEquals(10600000, KasBesar::latest('id')->first()->saldo);
        $this->assertSame('IUP', KasBesar::latest('id')->first()->nama_rek);
        $this->post($url, ['alasan' => 'Dana dikembalikan', 'password' => 'confirm-test'])->assertSessionHasErrors('alasan');
        $this->assertSame(2, KasBesar::count());
    }

    public function test_payroll_deducts_loans_and_final_rounding_once(): void
    {
        $employee = KaryawanFactory::new()->create();
        $loan = KasbonFactory::new()->installment()->create(['karyawan_id' => $employee->id, 'nominal' => 1000000, 'sisa_kas' => 1000000, 'cicilan_nominal' => 333333]);
        $this->fund();
        $payroll = app(EmployeePayrollService::class);
        $rekap = $payroll->store();
        $this->assertEquals(3666667, $rekap->total);
        $this->assertEquals(333333, RekapGajiDetail::sole()->potongan_kasbon);
        $this->assertEquals(666667, $loan->fresh()->sisa_kas);
        $this->post(route('billing.form-cost-operational.gaji.store'), ['total' => 1])->assertSessionHasErrors('total');
        $this->assertEquals(666667, $loan->fresh()->sisa_kas);
        $this->travelTo(Carbon::parse('2026-11-20'));
        $payroll->store();
        $this->travelTo(Carbon::parse('2026-12-20'));
        $payroll->store();
        $this->assertTrue($loan->fresh()->lunas);
        $this->assertEquals(1000000, $loan->fresh()->total_bayar);
        $this->assertEquals(333334, RekapGajiDetail::latest('id')->first()->potongan_kasbon);
    }

    public function test_insufficient_cash_and_failed_insert_roll_back_all_writes(): void
    {
        $employee = KaryawanFactory::new()->create();
        $this->fund(100);
        $this->post(route('billing.kasbon.store'), $this->payload($employee))->assertSessionHasErrors('nominal');
        $this->assertSame(0, Kasbon::count());
        $this->assertSame(1, KasBesar::count());
        $this->fund();
        DB::statement("CREATE TRIGGER reject_loan BEFORE INSERT ON kasbons BEGIN SELECT RAISE(ABORT, 'test rollback'); END");
        try {
            app(KasbonService::class)->create($this->payload($employee));
            $this->fail('Expected database failure.');
        } catch (QueryException) {
            $this->assertSame(2, KasBesar::count());
            $this->assertSame(0, Kasbon::count());
        }
    }

    public function test_insufficient_payroll_cash_does_not_reduce_debt(): void
    {
        $employee = KaryawanFactory::new()->create();
        $loan = KasbonFactory::new()->create(['karyawan_id' => $employee->id]);
        $this->fund(1);
        $this->post(route('billing.form-cost-operational.gaji.store'), ['total' => 0])->assertSessionHasErrors('total');
        $this->assertEquals(100000, $loan->fresh()->sisa_kas);
        $this->assertSame(0, RekapGaji::count());
    }

    public function test_reports_period_filters_print_and_guest_authorization(): void
    {
        $employee = KaryawanFactory::new()->create(['nama' => '<b>Nama uji</b>']);
        KasbonFactory::new()->create(['karyawan_id' => $employee->id]);
        KasbonFactory::new()->create(['karyawan_id' => $employee->id, 'tanggal' => '2026-09-20']);
        $this->get(route('rekap.kasbon'))->assertOk()->assertSee('<b>Nama uji</b>')->assertDontSee('<b>Nama uji</b>', false)->assertViewHas('loans', fn ($loans) => $loans->total() === 1);
        $this->get(route('rekap.kasbon', ['bulan' => 9]))->assertOk()->assertViewHas('loans', fn ($loans) => $loans->total() === 1);
        $this->get(route('rekap.kasbon', ['print' => 1]))->assertOk()->assertSee('window.print()', false);
        $this->get(route('billing.form-cost-operational.gaji'))->assertOk()->assertSee('Potongan Kasbon');
        $this->get(route('billing.kasbon.create', ['jenis' => 'cicilan']))->assertOk()->assertSee('Jumlah cicilan');
        $this->app['auth']->forgetGuards();
        $this->get(route('rekap.kasbon'))->assertRedirect('/login');
        $this->post(route('billing.kasbon.store'), $this->payload($employee))->assertRedirect('/login');
    }

    public function test_loan_cannot_be_added_after_payroll_and_payroll_ignores_client_total(): void
    {
        $employee = KaryawanFactory::new()->create(['apa_bpjs_tk' => 1, 'apa_bpjs_kes' => 1]);
        $this->fund();
        $this->post(route('billing.form-cost-operational.gaji.store'), ['total' => 1])->assertRedirect(route('billing.form-cost-operational'));
        $this->assertEquals(3910000, RekapGaji::sole()->total);
        $this->post(route('billing.kasbon.store'), $this->payload($employee))->assertSessionHasErrors('nominal');
        $this->assertSame(0, Kasbon::count());
    }

    public function test_paid_and_void_loans_do_not_block_or_reduce_limits(): void
    {
        $employee = KaryawanFactory::new()->create();
        KasbonFactory::new()->installment()->create(['karyawan_id' => $employee->id, 'lunas' => true, 'total_bayar' => 900000, 'sisa_kas' => 0]);
        KasbonFactory::new()->create(['karyawan_id' => $employee->id, 'void' => true, 'sisa_kas' => 0]);
        $this->fund();
        $summary = app(KasbonService::class)->summary($employee, now());
        $this->assertSame(2000000, $summary['ordinary_available']);
        $this->post(route('billing.kasbon.store'), $this->payload($employee, ['jenis' => 'cicilan', 'cicil_kali' => 2, 'mulai_bulan' => 11, 'mulai_tahun' => 2026]))->assertSessionHasNoErrors();
    }

    protected function tearDown(): void
    {
        DB::purge('kasbon_test');
        parent::tearDown();
    }
}
