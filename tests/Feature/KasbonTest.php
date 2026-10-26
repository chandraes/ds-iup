<?php

namespace Tests\Feature;

use App\Models\db\Jabatan;
use App\Models\db\Karyawan;
use App\Models\KasBesar;
use App\Models\Kasbon;
use App\Models\PasswordKonfirmasi;
use App\Models\PesanWa;
use App\Models\RekapGaji;
use App\Models\RekapGajiDetail;
use App\Models\Rekening;
use App\Models\User;
use App\Services\EmployeePayrollService;
use App\Services\KasbonMessage;
use App\Services\KasbonService;
use App\Services\PayrollAttendanceService;
use Carbon\Carbon;
use Database\Factories\KaryawanFactory;
use Database\Factories\KasbonFactory;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
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
        Schema::create('pesan_was', function (Blueprint $table): void {
            $table->id();
            $table->longText('pesan');
            $table->string('tujuan');
            $table->boolean('status');
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
        (require database_path('migrations/2026_10_24_151732_add_sales_components_and_period_dates_to_payroll_tables.php'))->up();
        (require database_path('migrations/2026_10_24_153457_add_commission_calculation_snapshot_to_rekap_gaji_details_table.php'))->up();
        (require database_path('migrations/2026_10_24_214601_add_attendance_calculations_to_payroll_tables.php'))->up();
        (require database_path('migrations/2026_10_16_202122_create_attendance_employee_syncs_table.php'))->up();
        Schema::create('invoice_juals', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('karyawan_id')->nullable();
            $table->boolean('titipan')->default(false);
            $table->boolean('void')->default(false);
            $table->boolean('lunas')->default(false);
            $table->bigInteger('grand_total');
            $table->timestamps();
        });
        (require glob(database_path('migrations/*_create_bpjs_health_minimums_table.php'))[0])->up();
        foreach (range(2020, 2030) as $year) {
            DB::table('bpjs_health_minimums')->insert(['year' => $year, 'minimum_wage' => 1, 'basis' => 'UMK', 'reference' => 'Fixture pengujian']);
        }
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

    #[TestWith([false])]
    #[TestWith([true])]
    public function test_ordinary_loan_is_once_per_employee_per_period_even_when_paid(bool $sales): void
    {
        $employee = KaryawanFactory::new()->create();
        if ($sales) {
            $employee->update(['jabatan_id' => Jabatan::create(['nama' => 'Sales'])->id, 'persen_komisi' => 4]);
        }
        KasbonFactory::new()->create(['karyawan_id' => $employee->id, 'lunas' => true, 'sisa_kas' => 0, 'total_bayar' => 100000]);
        $cashCount = KasBesar::count();

        $this->post(route('billing.kasbon.store'), $this->payload($employee))->assertSessionHasErrors('karyawan_id');

        $this->assertSame(1, Kasbon::count());
        $this->assertSame($cashCount, KasBesar::count());
    }

    public function test_void_ordinary_loan_can_be_replaced_and_next_period_is_independent(): void
    {
        $employee = KaryawanFactory::new()->create();
        $this->fund();
        KasbonFactory::new()->create(['karyawan_id' => $employee->id, 'void' => true, 'sisa_kas' => 0]);
        $this->post(route('billing.kasbon.store'), $this->payload($employee))->assertSessionHasNoErrors();
        $this->post(route('billing.kasbon.store'), $this->payload($employee))->assertSessionHasErrors('karyawan_id');

        $this->travelTo(Carbon::parse('2026-10-26 10:00:00'));
        $this->post(route('billing.kasbon.store'), $this->payload($employee))->assertSessionHasNoErrors();
        $this->assertSame('2026-11-01', Kasbon::latest('id')->first()->mulai_periode->toDateString());
        $this->assertSame(3, Kasbon::count());
    }

    public function test_sales_ordinary_limit_uses_commission_to_today_minus_current_installment(): void
    {
        $employee = KaryawanFactory::new()->create(['jabatan_id' => Jabatan::create(['nama' => 'Sales'])->id, 'persen_komisi' => 4]);
        $this->fund();
        foreach ([['2026-09-25', 99000000, false, false, '2026-09-25'], ['2026-09-26', 20000000, false, false, '2026-09-26'],
            ['2026-10-20', 10000000, false, false, '2026-10-20'], ['2026-10-21', 99000000, false, false, '2026-10-21'],
            ['2026-10-19', 99000000, true, false, '2026-10-19'], ['2026-09-01', 5000000, false, true, '2026-10-20']] as [$date, $total, $consignment, $void, $updated]) {
            DB::table('invoice_juals')->insert(['karyawan_id' => $employee->id, 'grand_total' => $total, 'titipan' => $consignment,
                'void' => $void, 'created_at' => $date.' 09:00:00', 'updated_at' => $updated.' 09:00:00']);
        }
        KasbonFactory::new()->installment()->create(['karyawan_id' => $employee->id]);
        $this->get(route('billing.kasbon.create'))->assertOk()->assertSee($employee->nama);
        $this->getJson(route('billing.kasbon.summary', ['karyawan_id' => $employee->id, 'bulan' => 10, 'tahun' => 2026]))
            ->assertOk()->assertJsonPath('income', 1000000)->assertJsonPath('ordinary_available', 200000)->assertJsonPath('installment_limit', null);
        $this->post(route('billing.kasbon.store'), $this->payload($employee, ['nominal' => 200001]))->assertSessionHasErrors('nominal');
        $this->post(route('billing.kasbon.store'), $this->payload($employee, ['nominal' => 200000]))->assertSessionHasNoErrors();
        $this->assertSame(2, Kasbon::count());
    }

    public function test_sales_installment_has_no_income_cap_but_preserves_active_loan_and_cash_checks(): void
    {
        $employee = KaryawanFactory::new()->create(['jabatan_id' => Jabatan::create(['nama' => 'Sales'])->id, 'persen_komisi' => 4]);
        $this->fund();
        $payload = $this->payload($employee, ['jenis' => 'cicilan', 'nominal' => 25000000, 'cicil_kali' => 2, 'mulai_bulan' => 10, 'mulai_tahun' => 2026]);
        $this->post(route('billing.kasbon.store'), $payload)->assertSessionHasErrors('nominal');
        $payload['nominal'] = 10000000;
        $this->post(route('billing.kasbon.store'), $payload)->assertSessionHasNoErrors();
        $payload['submission_token'] = (string) Str::uuid();
        $this->post(route('billing.kasbon.store'), $payload)->assertSessionHasErrors('karyawan_id');
        $this->assertSame(1, Kasbon::count());
        $this->assertSame(10000000, (int) KasBesar::latest('id')->first()->saldo);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    #[TestWith(['kasbon', true])]
    #[TestWith(['kasbon', false])]
    #[TestWith(['void', true])]
    #[TestWith(['void', false])]
    #[TestWith(['gaji', true])]
    #[TestWith(['gaji', false])]
    public function test_payroll_and_loan_notifications_are_recorded_in_message_history(string $operation, bool $sent): void
    {
        $employee = KaryawanFactory::new()->create();
        $this->fund();
        DB::table('group_was')->insert(['untuk' => 'kas-besar-ppn', 'nama_group' => 'Grup PPN Uji']);
        $sender = \Mockery::mock('overload:App\\Services\\StarSender');
        $sender->shouldReceive('sendGroup')->once()->andReturn($sent);

        if ($operation === 'gaji') {
            $response = $this->post(route('billing.form-cost-operational.gaji.store'), ['periode' => '2026-10']);
        } elseif ($operation === 'void') {
            $loan = KasbonFactory::new()->create(['karyawan_id' => $employee->id]);
            Rekening::create(['untuk' => 'kas-besar-ppn', 'bank' => 'Bank Uji', 'no_rek' => '001', 'nama_rek' => 'IUP']);
            PasswordKonfirmasi::create(['password' => 'confirm-test']);
            $response = $this->post(route('rekap.kasbon.cancel', $loan), ['alasan' => 'Dibatalkan untuk pengujian', 'password' => 'confirm-test']);
        } else {
            $response = $this->post(route('billing.kasbon.store'), $this->payload($employee));
        }

        $response->assertSessionHasNoErrors();
        $this->assertSame(1, PesanWa::count());
        $message = PesanWa::sole();
        $this->assertSame('Grup PPN Uji', $message->tujuan);
        $this->assertSame($sent ? 1 : 0, (int) $message->status);
        $this->assertStringContainsString($operation === 'gaji' ? '*FORM GAJI KARYAWAN*' : ($operation === 'void' ? '*Form Void Kasbon Staff*' : '*Form Kasbon Staff*'), $message->pesan);
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
        $this->post(route('billing.kasbon.store'), $this->payload($employee))->assertSessionHasErrors('nominal');
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
        $this->post(route('billing.form-cost-operational.gaji.store'), ['periode' => now()->format('Y-m'), 'total' => 1])->assertSessionHasErrors('total');
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
        $this->post(route('billing.form-cost-operational.gaji.store'), ['periode' => now()->format('Y-m'), 'total' => 0])->assertSessionHasErrors('total');
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

    #[TestWith([1, 1, 3000000, 195600, 160000, 80000, 40000])]
    #[TestWith([1, 0, 3000000, 195600, 0, 80000, 0])]
    #[TestWith([0, 1, 3000000, 0, 160000, 0, 40000])]
    #[TestWith([0, 0, 3000000, 0, 0, 0, 0])]
    #[TestWith([1, 1, 0, 48900, 40000, 20000, 10000])]
    #[TestWith([1, 1, 11000000, 586800, 480000, 240000, 120000])]
    #[TestWith([1, 1, 14000000, 733500, 480000, 300000, 120000])]
    #[TestWith([0, 0, 14000000, 0, 0, 0, 0])]
    public function test_bpjs_uses_base_salary_and_fixed_allowances_only(int $tkEnabled, int $kesEnabled, int $baseSalary, int $companyTk, int $companyKes, int $employeeTk, int $employeeKes): void
    {
        $employee = KaryawanFactory::new()->create([
            'gaji_pokok' => $baseSalary, 'tunjangan_jabatan' => 600000, 'tunjangan_keluarga' => 400000,
            'apa_bpjs_tk' => $tkEnabled, 'apa_bpjs_kes' => $kesEnabled, 'tunjangan_bbm' => 30000,
        ]);

        $amounts = app(EmployeePayrollService::class)->amounts($employee, now(), commission: ['komisi' => 750000], payments: []);

        $this->assertSame($companyTk, $amounts['bpjs_tk']);
        $this->assertSame($companyKes, $amounts['bpjs_k']);
        $this->assertSame($employeeTk, $amounts['potongan_bpjs_tk']);
        $this->assertSame($employeeKes, $amounts['potongan_bpjs_kesehatan']);
        $this->assertSame($baseSalary + 1750000 - $employeeTk - $employeeKes, $amounts['pendapatan_bersih']);
    }

    public function test_missing_bpjs_year_blocks_payroll_without_remote_lock_or_cash_write(): void
    {
        KaryawanFactory::new()->create();
        $cash = $this->fund();
        DB::table('bpjs_health_minimums')->where('year', 2026)->delete();
        Http::preventStrayRequests();

        $this->post(route('billing.form-cost-operational.gaji.store'), ['periode' => '2026-10'])->assertSessionHasErrors('bpjs_health');

        $this->assertSame(0, RekapGaji::count());
        $this->assertEquals(20000000, $cash->fresh()->saldo);
        Http::assertNothingSent();
    }

    public function test_salary_preview_and_storage_use_floor_for_payroll_year(): void
    {
        KaryawanFactory::new()->create(['gaji_pokok' => 2000000, 'tunjangan_jabatan' => 500000, 'tunjangan_keluarga' => 500000, 'apa_bpjs_tk' => 1, 'apa_bpjs_kes' => 1]);
        $this->fund();
        DB::table('bpjs_health_minimums')->where('year', 2026)->update(['minimum_wage' => 4500000]);
        $this->get(route('billing.form-cost-operational.gaji'))->assertOk()->assertSee('45.000');
        $this->post(route('billing.form-cost-operational.gaji.store'), ['periode' => '2026-10'])->assertSessionHasNoErrors();
        $detail = RekapGajiDetail::sole();
        $this->assertEquals(180000, $detail->bpjs_k);
        $this->assertEquals(45000, $detail->potongan_bpjs_kesehatan);
        $this->assertEquals(60000, $detail->potongan_bpjs_tk);
        DB::table('bpjs_health_minimums')->where('year', 2026)->update(['minimum_wage' => 5000000]);
        $this->assertEquals(45000, $detail->fresh()->potongan_bpjs_kesehatan);
    }

    public function test_loan_cannot_be_added_after_payroll_and_payroll_ignores_client_total(): void
    {
        $employee = KaryawanFactory::new()->create(['apa_bpjs_tk' => 1, 'apa_bpjs_kes' => 1]);
        $this->fund();
        $this->post(route('billing.form-cost-operational.gaji.store'), ['periode' => now()->format('Y-m'), 'total' => 1])->assertRedirect(route('billing.form-cost-operational'));
        $this->assertEquals(3880000, RekapGaji::sole()->total);
        $detail = RekapGajiDetail::sole();
        $this->assertEquals(195600, $detail->bpjs_tk);
        $this->assertEquals(160000, $detail->bpjs_k);
        $this->assertEquals(80000, $detail->potongan_bpjs_tk);
        $this->assertEquals(40000, $detail->potongan_bpjs_kesehatan);
        $employee->update(['tunjangan_jabatan' => 900000]);
        $this->assertEquals(195600, $detail->fresh()->bpjs_tk);
        $this->assertEquals(3880000, RekapGaji::sole()->total);
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

    public function test_delayed_payroll_uses_oldest_missing_period_and_25th_cutoff(): void
    {
        $employee = KaryawanFactory::new()->create(['apa_bpjs_tk' => 1]);
        $this->fund();
        RekapGaji::create(['bulan' => 9, 'tahun' => 2026, 'uraian' => 'Gaji September', 'total' => 0]);
        $due = KasbonFactory::new()->create(['karyawan_id' => $employee->id, 'tanggal' => '2026-10-25']);
        $next = KasbonFactory::new()->create(['karyawan_id' => $employee->id, 'tanggal' => '2026-10-26']);
        $installment = KasbonFactory::new()->installment()->create(['karyawan_id' => $employee->id, 'tanggal' => '2026-10-26', 'mulai_periode' => '2026-11-01']);
        $this->travelTo(Carbon::parse('2026-11-10 10:00:00'));

        $this->get(route('billing.form-cost-operational.gaji'))->assertOk()->assertSee('Oktober')
            ->assertSee('26 September 2026')->assertSee('25 Oktober 2026')->assertSee('Komisi')->assertSee('Tunjangan BBM')
            ->assertViewHas('period', fn (Carbon $period): bool => $period->format('Y-m') === '2026-10');
        $this->post(route('billing.form-cost-operational.gaji.store'), ['periode' => '2026-11'])->assertSessionHasErrors('total');
        $this->assertSame(1, RekapGaji::count());
        $this->post(route('billing.form-cost-operational.gaji.store'), ['periode' => '2026-10', 'total' => 1])->assertSessionHasNoErrors();

        $record = RekapGaji::where('bulan', 10)->sole();
        $this->assertSame('2026-09-26', $record->periode_mulai);
        $this->assertSame('2026-10-25', $record->periode_selesai);
        $this->assertSame('2026-11-10', $record->created_at->toDateString());
        $detail = $record->details()->sole();
        $this->assertEquals(100000, $detail->potongan_kasbon);
        $this->assertEquals(195600, $detail->bpjs_tk);
        $this->assertEquals(0, $detail->komisi);
        $this->assertEquals(0, $detail->tunjangan_bbm);
        $this->get(route('rekap.gaji.detail', ['bulan' => 10, 'tahun' => 2026]))->assertOk()
            ->assertSee('Komisi')->assertSee('Tunjangan BBM')->assertSee('26 September 2026')->assertSee('25 Oktober 2026');
        $this->assertTrue($due->fresh()->lunas);
        $this->assertEquals(100000, $next->fresh()->sisa_kas);
        $this->assertEquals(0, $installment->fresh()->total_bayar);
        $this->post(route('billing.form-cost-operational.gaji.store'), ['periode' => '2026-10'])->assertSessionHasErrors('total');
        $this->assertSame(2, RekapGaji::count());
        $this->get(route('billing.form-cost-operational.gaji'))->assertOk()
            ->assertViewHas('period', fn (Carbon $period): bool => $period->format('Y-m') === '2026-11');
    }

    public function test_missing_period_is_selected_even_when_newer_payroll_exists(): void
    {
        foreach ([9, 11] as $month) {
            RekapGaji::create(['bulan' => $month, 'tahun' => 2026, 'uraian' => 'Riwayat gaji', 'total' => 0]);
        }
        $this->travelTo(Carbon::parse('2026-12-10'));
        $this->assertSame('2026-10', app(EmployeePayrollService::class)->nextPeriod()->format('Y-m'));
    }

    public function test_pending_period_and_submission_cutoff_handle_year_rollover(): void
    {
        RekapGaji::create(['bulan' => 11, 'tahun' => 2025, 'uraian' => 'Gaji November', 'total' => 0]);
        $this->travelTo(Carbon::parse('2026-01-10'));
        $this->assertSame('2025-12', app(EmployeePayrollService::class)->nextPeriod()->format('Y-m'));
        $this->travelTo(Carbon::parse('2026-12-26'));
        $this->assertSame('2027-01', app(KasbonService::class)->earliestSubmissionPeriod()->format('Y-m'));
    }

    public function test_loans_after_25th_are_assigned_to_next_period(): void
    {
        $employee = KaryawanFactory::new()->create();
        $this->fund();
        $this->travelTo(Carbon::parse('2026-10-26'));
        $this->post(route('billing.kasbon.store'), $this->payload($employee))->assertSessionHasNoErrors();
        $loan = Kasbon::sole();
        $this->assertSame('2026-11-01', $loan->mulai_periode->toDateString());
        $service = app(KasbonService::class);
        $this->assertSame([], $service->payments($employee, Carbon::parse('2026-10-01')));
        $this->assertCount(1, $service->payments($employee, Carbon::parse('2026-11-01')));
        $this->assertSame(0, $service->summary($employee, Carbon::parse('2026-10-01'))['ordinary_due']);
        $this->assertSame(1000000, $service->summary($employee, Carbon::parse('2026-11-01'))['ordinary_due']);
        $this->post(route('billing.kasbon.store'), $this->payload($employee, ['jenis' => 'cicilan', 'cicil_kali' => 2, 'mulai_bulan' => 10, 'mulai_tahun' => 2026]))->assertSessionHasErrors('mulai_bulan');
        $this->get(route('billing.kasbon.create', ['jenis' => 'cicilan']))->assertOk()
            ->assertViewHas('minimumPeriod', fn (Carbon $period): bool => $period->format('Y-m') === '2026-11');
    }

    public function test_payroll_requires_the_displayed_period(): void
    {
        $this->post(route('billing.form-cost-operational.gaji.store'), [])->assertSessionHasErrors('periode');
        $this->post(route('billing.form-cost-operational.gaji.store'), ['periode' => '2026-13'])->assertSessionHasErrors('periode');
        $this->assertSame(0, RekapGaji::count());
    }

    public function test_sales_commission_matches_turnover_and_is_saved_with_its_calculation(): void
    {
        $sales = KaryawanFactory::new()->create(['jabatan_id' => Jabatan::create(['nama' => 'SALES'])->id, 'persen_komisi' => '4.00']);
        $staff = KaryawanFactory::new()->create(['persen_komisi' => '99.00']);
        $this->fund();
        foreach ([['2026-09-26 00:00:00', 1000000, []], ['2026-10-25 23:59:59', 2000000, []],
            ['2026-09-25 23:59:59', 9000000, []], ['2026-10-26 00:00:00', 8000000, []],
            ['2026-10-05 12:00:00', 7000000, ['titipan' => true]],
            ['2026-08-01 12:00:00', 500000, ['void' => true, 'updated_at' => '2026-10-10 12:00:00']],
            ['2026-10-05 12:00:00', 400000, ['void' => true]]] as [$date, $amount, $extra]) {
            DB::table('invoice_juals')->insert(array_replace(['karyawan_id' => $sales->id, 'grand_total' => $amount, 'created_at' => $date, 'updated_at' => $date], $extra));
        }
        DB::table('invoice_juals')->insert(['karyawan_id' => $staff->id, 'grand_total' => 9000000, 'created_at' => '2026-10-05 12:00:00', 'updated_at' => '2026-10-05 12:00:00']);
        $payroll = app(EmployeePayrollService::class);
        $rows = $payroll->previewRows(Karyawan::with('jabatan')->get(), Carbon::parse('2026-10-01'))->sortByDesc('jabatan')->values();
        $this->assertSame(2500000, $rows[0]['omset_komisi']);
        $this->assertSame(100000, $rows[0]['komisi']);
        $this->assertSame(4100000, $rows[0]['pendapatan_bersih']);
        $this->assertSame(4100000, $rows[0]['pendapatan_kotor']);
        $this->assertSame(0, $rows[1]['komisi']);
        $this->assertNull($rows[1]['rincian_komisi']);
        $this->get(route('billing.form-cost-operational.gaji'))->assertOk()->assertSee('Lihat rincian komisi '.$sales->nama)
            ->assertSee('data-bs-target="#payroll-commission"', false)->assertDontSee('Omset bersih × persentase komisi');
        $this->getJson(route('billing.form-cost-operational.gaji.komisi', ['karyawan_id' => $sales->id, 'periode' => '2026-10']))->assertOk()
            ->assertJsonPath('title', 'Komisi '.$sales->nama);
        $this->post(route('billing.form-cost-operational.gaji.store'), ['periode' => '2026-10', 'komisi' => 999999999])->assertSessionHasNoErrors();
        $record = RekapGajiDetail::where('nama', $sales->nama)->sole();
        $this->assertEquals(8100000, RekapGaji::sole()->total);
        $this->assertEquals(100000, $record->komisi);
        $this->assertEquals(2500000, $record->omset_komisi);
        $this->assertEquals(4, $record->persen_komisi);
        $this->assertCount(30, $record->rincian_komisi['harian']);
        $this->assertSame(900000, $record->rincian_komisi['void']);
        $this->assertEquals(11900000, KasBesar::latest('id')->first()->saldo);
        $sales->update(['persen_komisi' => 10]);
        DB::table('invoice_juals')->update(['grand_total' => 1]);
        $this->get(route('rekap.gaji.detail', ['bulan' => 10, 'tahun' => 2026]))->assertOk()->assertSee(route('rekap.gaji.komisi', ['detail' => $record->id]))
            ->assertViewHas('rows', fn ($rows): bool => ! array_key_exists('rincian_komisi', $rows[0]));
        $snapshot = $this->getJson(route('rekap.gaji.komisi', ['detail' => $record->id]))->assertOk();
        $this->assertStringContainsString('4,00%', $snapshot->json('html'));
        $this->assertStringContainsString('2.500.000', $snapshot->json('html'));
        $this->assertEquals(100000, $record->fresh()->komisi);
    }

    public function test_commission_rounds_monthly_total_and_can_include_negative_void_corrections(): void
    {
        $sales = KaryawanFactory::new()->create(['jabatan_id' => Jabatan::create(['nama' => 'Sales'])->id, 'persen_komisi' => '2.55']);
        DB::table('invoice_juals')->insert(['karyawan_id' => $sales->id, 'grand_total' => 101, 'created_at' => '2026-10-01', 'updated_at' => '2026-10-01']);
        $payroll = app(EmployeePayrollService::class);
        $this->assertSame(3, $payroll->amounts($sales, now())['komisi']);
        DB::table('invoice_juals')->update(['created_at' => '2026-08-01', 'void' => true]);
        $this->fund();
        $this->post(route('billing.form-cost-operational.gaji.store'), ['periode' => '2026-10'])->assertSessionHasNoErrors();
        $record = RekapGajiDetail::sole();
        $this->assertEquals(-3, $record->komisi);
        $this->assertEquals(-101, $record->omset_komisi);
        $this->assertEquals(3999997, $record->sisa_gaji_dibayar);
    }

    public function test_payroll_preview_reads_missing_attendance_period_only_once(): void
    {
        config(['attendance.enabled' => true]);
        KaryawanFactory::new()->count(3)->create();
        DB::enableQueryLog();
        DB::flushQueryLog();

        try {
            $response = $this->get(route('billing.form-cost-operational.gaji'));
            $queries = collect(DB::getQueryLog());
        } finally {
            DB::disableQueryLog();
        }

        $response->assertOk()->assertViewHas('attendancePeriod', null);
        $this->assertCount(1, $queries->filter(fn ($query): bool => str_contains($query['query'], 'select * from "payroll_attendance_periods"')));
    }

    public function test_payroll_batches_loan_reads_and_groups_sales_below_other_positions(): void
    {
        $salesJob = Jabatan::create(['nama' => 'Sales']);
        $adminJob = Jabatan::create(['nama' => 'Admin']);
        $driverJob = Jabatan::create(['nama' => 'Driver']);
        foreach ([['Sales Z', $salesJob], ['Admin Z', $adminJob], ['Sales A', $salesJob], ['Driver A', $driverJob], ['Admin A', $adminJob]] as [$name, $job]) {
            $employee = KaryawanFactory::new()->create(['nama' => $name, 'jabatan_id' => $job->id, 'persen_komisi' => $job->id === $salesJob->id ? 4 : null]);
            KasbonFactory::new()->create(['karyawan_id' => $employee->id]);
        }
        $this->fund(30000000);
        DB::enableQueryLog();
        DB::flushQueryLog();
        $response = $this->get(route('billing.form-cost-operational.gaji'));
        $previewQueries = collect(DB::getQueryLog());
        DB::disableQueryLog();

        $response->assertOk()->assertViewHas('rows', fn ($rows): bool => $rows->pluck('nama')->all() === ['Admin A', 'Admin Z', 'Driver A', 'Sales A', 'Sales Z'])
            ->assertViewHas('data', fn ($people): bool => ! array_key_exists('alamat', $people->first()->getAttributes()))
            ->assertDontSee('Omset bersih × persentase komisi');
        $this->assertCount(1, $previewQueries->filter(fn ($query): bool => str_contains($query['query'], 'from "kasbons"')));
        $this->assertCount(2, $previewQueries->filter(fn ($query): bool => str_contains($query['query'], 'from "invoice_juals"')));

        DB::enableQueryLog();
        DB::flushQueryLog();
        $this->post(route('billing.form-cost-operational.gaji.store'), ['periode' => '2026-10'])->assertSessionHasNoErrors();
        $storeQueries = collect(DB::getQueryLog());
        DB::disableQueryLog();
        $this->assertCount(1, $storeQueries->filter(fn ($query): bool => str_contains($query['query'], 'from "kasbons"')));
        $this->assertEquals(19500000, RekapGaji::sole()->total);
        $this->assertSame(0, Kasbon::where('lunas', false)->count());
        $this->get(route('rekap.gaji.detail', ['bulan' => 10, 'tahun' => 2026]))->assertOk()
            ->assertViewHas('rows', fn ($rows): bool => $rows->pluck('nama')->all() === ['Admin A', 'Admin Z', 'Driver A', 'Sales A', 'Sales Z']);
    }

    public function test_lazy_commission_endpoint_validates_period_employee_and_access(): void
    {
        $sales = KaryawanFactory::new()->create(['jabatan_id' => Jabatan::create(['nama' => 'Sales'])->id, 'persen_komisi' => 4]);
        $staff = KaryawanFactory::new()->create();
        $url = route('billing.form-cost-operational.gaji.komisi');
        $this->getJson($url)->assertUnprocessable()->assertJsonValidationErrors(['karyawan_id', 'periode']);
        $this->getJson($url.'?karyawan_id='.$staff->id.'&periode=2026-10')->assertNotFound();
        $this->getJson($url.'?karyawan_id='.$sales->id.'&periode=2026-11')->assertUnprocessable()->assertJsonValidationErrors('periode');
        $sales->update(['status' => 0]);
        $this->getJson($url.'?karyawan_id='.$sales->id.'&periode=2026-10')->assertNotFound();
        $this->fund();
        $detail = app(EmployeePayrollService::class)->store()->details()->first();
        $user = new User(['name' => 'Sales uji', 'role' => 'sales']);
        $user->id = 998;
        $this->actingAs($user)->getJson($url.'?karyawan_id='.$sales->id.'&periode=2026-10')->assertForbidden();
        $this->getJson(route('rekap.gaji.komisi', ['detail' => $detail->id]))->assertForbidden();
        $this->app['auth']->forgetGuards();
        $this->getJson($url.'?karyawan_id='.$sales->id.'&periode=2026-10')->assertUnauthorized();
    }

    private function attendanceSnapshot(array $employees, array $overrides = [], bool $ready = true): array
    {
        $this->travelTo(Carbon::parse('2026-10-26 10:00:00'));
        config(['attendance.enabled' => true, 'attendance.url' => 'https://attendance.test', 'attendance.token' => str_repeat('t', 64), 'attendance.send_immediately' => false]);
        $sources = array_map(fn (Karyawan $employee): string => (string) $employee->id, $employees);
        sort($sources, SORT_STRING);
        $people = [];
        foreach ($employees as $employee) {
            $days = [];
            for ($date = Carbon::parse('2026-09-26'); $date->lte(Carbon::parse('2026-10-25')); $date->addDay()) {
                $key = $date->toDateString();
                $days[] = array_replace(['date' => $key, 'status' => 'off', 'schedule_mode' => 'weekly', 'schedule_name' => null, 'scheduled_minutes' => 0, 'break_minutes' => 0, 'late_minutes' => 0, 'early_minutes' => 0, 'check_in_late_minutes' => 0, 'check_in_at' => null, 'check_out_at' => null], $overrides[$employee->id][$key] ?? []);
            }
            $people[] = ['source_employee_id' => (string) $employee->id, 'attendance_required' => true, 'days' => $days];
        }
        $snapshot = ['schema_version' => 1, 'monitoring_version' => 1, 'fuel_allowance_version' => 1, 'from' => '2026-09-26', 'to' => '2026-10-25', 'timezone' => 'Asia/Jakarta', 'source_employee_ids' => $sources, 'employees' => $people, 'issues' => $ready ? [] : ['Ada izin menunggu persetujuan.'], 'ready' => $ready, 'as_of' => now()->toIso8601String()];
        $snapshot['revision'] = hash('sha256', json_encode($snapshot));

        return $snapshot;
    }

    private function fakeAttendance(array $snapshot): void
    {
        Http::swap(new Factory);
        Http::preventStrayRequests();
        Http::fake(['https://attendance.test/api/v1/integration/payroll-snapshot' => fn ($request) => Http::response(['data' => isset($request['request_id']) ? $snapshot + ['lock_request_id' => $request['request_id']] : $snapshot])]);
    }

    private function attendanceDay(string $status, int $late = 0, int $early = 0): array
    {
        return ['status' => $status, 'scheduled_minutes' => 540, 'break_minutes' => 60, 'late_minutes' => $status === 'present' ? $late : 0, 'early_minutes' => $early, 'check_in_late_minutes' => $late,
            'check_in_at' => in_array($status, ['present', 'half_day', 'in_progress'], true) ? '2026-10-01T09:00:00+07:00' : null];
    }

    public function test_attendance_calculates_non_sales_and_daily_sales_bbm_without_double_deductions(): void
    {
        $staff = KaryawanFactory::new()->create();
        $sales = KaryawanFactory::new()->create(['jabatan_id' => Jabatan::create(['nama' => 'SALES'])->id, 'gaji_pokok' => 0, 'tunjangan_jabatan' => 0, 'tunjangan_keluarga' => 0, 'tunjangan_bbm' => 30000, 'persen_komisi' => 0]);
        $snapshot = $this->attendanceSnapshot([$staff, $sales], [
            $staff->id => ['2026-10-01' => $this->attendanceDay('present', 48), '2026-10-02' => $this->attendanceDay('half_day', 96), '2026-10-03' => $this->attendanceDay('absent')],
            $sales->id => ['2026-10-01' => $this->attendanceDay('present', 48, 120), '2026-10-02' => $this->attendanceDay('half_day', 96), '2026-10-03' => $this->attendanceDay('absent'), '2026-10-04' => $this->attendanceDay('excused'), '2026-10-05' => ['status' => 'holiday'], '2026-10-07' => $this->attendanceDay('present', 600)],
        ]);
        $this->fakeAttendance($snapshot);
        $this->post(route('billing.form-cost-operational.gaji.absensi.refresh'), ['periode' => '2026-10'])->assertSessionHasNoErrors();
        $payroll = app(EmployeePayrollService::class);
        $rows = $payroll->previewRows($payroll->employees(), Carbon::parse('2026-10-01'))->keyBy('karyawan_id');
        $this->assertSame(256000, $rows[$staff->id]['potongan_absensi']);
        $this->assertSame(0, $rows[$staff->id]['tunjangan_bbm']);
        $this->assertSame(0, $rows[$sales->id]['potongan_absensi']);
        $this->assertSame(51000, $rows[$sales->id]['tunjangan_bbm']);
        $this->assertSame(51000, $rows[$sales->id]['sisa_gaji_dibayar']);
        $detail = $rows[$sales->id]['rincian_absensi'];
        $this->assertSame(90000, $detail['bbm_gross']);
        $this->assertSame(39000, $detail['bbm_reduction']);
        $this->assertSame(24000, collect($detail['days'])->firstWhere('date', '2026-10-02')['bbm_received']);
        $this->get(route('billing.form-cost-operational.gaji'))->assertOk()->assertSee('Lihat rincian BBM '.$sales->nama)->assertSee('Lihat rincian potongan absensi '.$staff->nama);
        $this->getJson(route('billing.form-cost-operational.gaji.absensi.detail', ['karyawan_id' => $sales->id, 'periode' => '2026-10']))->assertOk()->assertJsonPath('title', 'Tunjangan BBM '.$sales->nama);
        $this->fund();
        $this->post(route('billing.form-cost-operational.gaji.store'), ['periode' => '2026-10', 'attendance_revision' => $snapshot['revision']])->assertSessionHasNoErrors();
        $record = RekapGajiDetail::where('karyawan_id', $sales->id)->sole();
        $this->assertEquals(51000, $record->tunjangan_bbm);
        $this->assertSame(30000, $record->rincian_absensi['daily_rate']);
        $this->assertNotNull(DB::table('payroll_attendance_periods')->sole()->sealed_at);
        $sales->update(['tunjangan_bbm' => 90000, 'status' => 0]);
        config(['attendance.enabled' => false]);
        $this->getJson(route('rekap.gaji.absensi', ['detail' => $record->id]))->assertOk()->assertSee('30.000');
        $this->get(route('rekap.gaji.detail', ['bulan' => 10, 'tahun' => 2026]))->assertOk()->assertSee(route('rekap.gaji.absensi', ['detail' => $record->id]));
        $this->assertSame(30000, $record->fresh()->rincian_absensi['daily_rate']);
    }

    public function test_non_sales_cap_and_sales_tariff_change_are_calculated_before_payroll(): void
    {
        $staff = KaryawanFactory::new()->create();
        $sales = KaryawanFactory::new()->create(['jabatan_id' => Jabatan::create(['nama' => 'Sales'])->id, 'tunjangan_bbm' => 30000]);
        $days = [];
        for ($day = 1; $day <= 7; $day++) {
            $days[sprintf('2026-10-%02d', $day)] = $this->attendanceDay('absent');
        }
        $snapshot = $this->attendanceSnapshot([$staff, $sales], [$staff->id => $days, $sales->id => ['2026-10-01' => $this->attendanceDay('present', 48)]]);
        $this->fakeAttendance($snapshot);
        $service = app(PayrollAttendanceService::class);
        $service->refresh(Carbon::parse('2026-10-01'), $snapshot['source_employee_ids']);
        $detail = $service->calculate($staff->load('jabatan'), Carbon::parse('2026-10-01'));
        $this->assertSame(1120000, $detail['rincian_absensi']['raw']);
        $this->assertSame(1000000, $detail['potongan_absensi']);
        $sales->update(['tunjangan_bbm' => 40000]);
        $this->assertSame(36000, $service->calculate($sales->load('jabatan'), Carbon::parse('2026-10-01'))['tunjangan_bbm']);
        config(['attendance.enabled' => false]);
        $this->assertSame(0, $service->calculate($staff, Carbon::parse('2026-10-01'))['potongan_absensi']);
        $this->assertSame(0, $service->calculate($sales, Carbon::parse('2026-10-01'))['tunjangan_bbm']);
    }

    public function test_incomplete_or_changed_snapshot_blocks_payroll_without_cash_or_debt_changes(): void
    {
        $employee = KaryawanFactory::new()->create();
        $snapshot = $this->attendanceSnapshot([$employee], [], false);
        $this->fakeAttendance($snapshot);
        $this->fund();
        $this->post(route('billing.form-cost-operational.gaji.absensi.refresh'), ['periode' => '2026-10'])->assertSessionHasNoErrors();
        $this->get(route('billing.form-cost-operational.gaji'))->assertOk()->assertSee('Penggajian diblokir');
        $this->post(route('billing.form-cost-operational.gaji.store'), ['periode' => '2026-10', 'attendance_revision' => $snapshot['revision']])->assertSessionHasErrors('attendance');
        $this->assertSame(0, RekapGaji::count());
        $this->assertSame(1, KasBesar::count());
        $snapshot = $this->attendanceSnapshot([$employee]);
        $this->fakeAttendance($snapshot);
        $this->post(route('billing.form-cost-operational.gaji.absensi.refresh'), ['periode' => '2026-10'])->assertSessionHasNoErrors();
        $this->post(route('billing.form-cost-operational.gaji.store'), ['periode' => '2026-10', 'attendance_revision' => str_repeat('a', 64)])->assertSessionHasErrors('attendance');
        $this->assertNull(DB::table('payroll_attendance_periods')->sole()->lock_attempted_at);
    }

    public function test_lock_timeout_can_retry_same_request_without_duplicate_payroll(): void
    {
        $employee = KaryawanFactory::new()->create();
        $snapshot = $this->attendanceSnapshot([$employee]);
        $this->fakeAttendance($snapshot);
        $this->fund();
        $this->post(route('billing.form-cost-operational.gaji.absensi.refresh'), ['periode' => '2026-10'])->assertSessionHasNoErrors();
        $requestId = DB::table('payroll_attendance_periods')->sole()->request_id;
        Http::swap(new Factory);
        Http::preventStrayRequests();
        Http::fake(['https://attendance.test/api/v1/integration/payroll-snapshot' => Http::failedConnection()]);
        $data = ['periode' => '2026-10', 'attendance_revision' => $snapshot['revision']];
        $this->post(route('billing.form-cost-operational.gaji.store'), $data)->assertSessionHasErrors('attendance');
        $this->assertNotNull(DB::table('payroll_attendance_periods')->sole()->lock_attempted_at);
        $this->assertSame(0, RekapGaji::count());
        $this->post(route('billing.form-cost-operational.gaji.absensi.refresh'), ['periode' => '2026-10'])->assertSessionHasErrors('attendance');
        $this->fakeAttendance($snapshot);
        $this->post(route('billing.form-cost-operational.gaji.store'), $data)->assertSessionHasNoErrors();
        Http::assertSent(fn ($request): bool => $request['request_id'] === $requestId);
        $this->assertSame(1, RekapGaji::count());
        $this->assertSame(2, KasBesar::count());
        $this->post(route('billing.form-cost-operational.gaji.store'), $data)->assertSessionHasErrors('total');
        $this->assertSame(1, RekapGaji::count());
    }

    public function test_remote_revision_conflict_and_malformed_payload_are_rejected(): void
    {
        $employee = KaryawanFactory::new()->create();
        $snapshot = $this->attendanceSnapshot([$employee]);
        $this->fakeAttendance($snapshot);
        $this->fund();
        $this->post(route('billing.form-cost-operational.gaji.absensi.refresh'), ['periode' => '2026-10'])->assertSessionHasNoErrors();
        Http::swap(new Factory);
        Http::preventStrayRequests();
        Http::fake(['https://attendance.test/api/v1/integration/payroll-snapshot' => Http::response([], 409)]);
        $this->post(route('billing.form-cost-operational.gaji.store'), ['periode' => '2026-10', 'attendance_revision' => $snapshot['revision']])->assertSessionHasErrors('attendance');
        $this->assertNull(DB::table('payroll_attendance_periods')->sole()->lock_attempted_at);
        $this->assertSame(0, RekapGaji::count());
        unset($snapshot['fuel_allowance_version']);
        $this->fakeAttendance($snapshot);
        $this->post(route('billing.form-cost-operational.gaji.absensi.refresh'), ['periode' => '2026-10'])->assertSessionHasErrors('attendance');
    }

    public function test_disabled_attendance_does_not_contact_server_or_pay_configured_fuel_allowance(): void
    {
        $sales = KaryawanFactory::new()->create(['jabatan_id' => Jabatan::create(['nama' => 'Sales'])->id, 'tunjangan_bbm' => 30000]);
        Http::preventStrayRequests();
        $this->fund();
        $this->post(route('billing.form-cost-operational.gaji.store'), ['periode' => '2026-10'])->assertSessionHasNoErrors();
        $row = RekapGajiDetail::sole();
        $this->assertEquals(0, $row->potongan_absensi);
        $this->assertEquals(0, $row->tunjangan_bbm);
        $this->assertNull($row->rincian_absensi);
        Http::assertNothingSent();
    }

    public function test_snapshot_employee_mismatch_and_reader_mutations_are_rejected(): void
    {
        $employee = KaryawanFactory::new()->create();
        $snapshot = $this->attendanceSnapshot([$employee]);
        $snapshot['employees'][0]['source_employee_id'] = '999999';
        $this->fakeAttendance($snapshot);
        $this->post(route('billing.form-cost-operational.gaji.absensi.refresh'), ['periode' => '2026-10'])->assertSessionHasErrors('attendance');
        $this->assertSame(0, DB::table('payroll_attendance_periods')->count());
        $reader = new User(['name' => 'Reader', 'role' => 'sales']);
        $reader->id = 998;
        $this->actingAs($reader)->post(route('billing.form-cost-operational.gaji.absensi.refresh'), ['periode' => '2026-10'])->assertForbidden();
        $this->getJson(route('billing.form-cost-operational.gaji.absensi.detail', ['karyawan_id' => $employee->id, 'periode' => '2026-10']))->assertForbidden();
    }

    public function test_local_failure_after_remote_lock_keeps_request_for_safe_retry(): void
    {
        $employee = KaryawanFactory::new()->create();
        $snapshot = $this->attendanceSnapshot([$employee]);
        $this->fakeAttendance($snapshot);
        $this->fund();
        $this->post(route('billing.form-cost-operational.gaji.absensi.refresh'), ['periode' => '2026-10'])->assertSessionHasNoErrors();
        $requestId = DB::table('payroll_attendance_periods')->sole()->request_id;
        Http::swap(new Factory);
        Http::preventStrayRequests();
        Http::fake(['https://attendance.test/api/v1/integration/payroll-snapshot' => function ($request) use ($snapshot) {
            DB::table('kas_besars')->update(['saldo' => 0]);

            return Http::response(['data' => $snapshot + ['lock_request_id' => $request['request_id']]]);
        }]);
        $data = ['periode' => '2026-10', 'attendance_revision' => $snapshot['revision']];
        $this->post(route('billing.form-cost-operational.gaji.store'), $data)->assertSessionHasErrors('total');
        $this->assertSame(0, RekapGaji::count());
        $this->assertSame(0, RekapGajiDetail::count());
        $this->assertNotNull(DB::table('payroll_attendance_periods')->sole()->remote_locked_at);
        $this->assertNull(DB::table('payroll_attendance_periods')->sole()->sealed_at);
        $this->fund();
        $this->fakeAttendance($snapshot);
        $this->post(route('billing.form-cost-operational.gaji.store'), $data)->assertSessionHasNoErrors();
        Http::assertSent(fn ($request): bool => $request['request_id'] === $requestId);
        $this->assertSame(1, RekapGaji::count());
        $this->assertSame(1, RekapGajiDetail::count());
    }

    protected function tearDown(): void
    {
        DB::purge('kasbon_test');
        parent::tearDown();
    }
}
