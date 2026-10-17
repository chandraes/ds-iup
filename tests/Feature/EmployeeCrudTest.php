<?php

namespace Tests\Feature;

use App\Http\Controllers\DatabaseController;
use App\Http\Middleware\Authenticate;
use App\Models\AttendanceEmployeeSync;
use App\Models\db\Jabatan;
use App\Models\db\Karyawan;
use App\Models\User;
use App\Services\AttendanceConnector;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class EmployeeCrudTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['attendance.enabled' => false]);
        config(['database.default' => 'employee_crud_test', 'database.connections.employee_crud_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true]]);
        Schema::create('jabatans', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->timestamps();
        });
        Schema::create('karyawans', function (Blueprint $table): void {
            $table->id();
            $table->integer('nomor');
            $table->foreignId('jabatan_id')->constrained('jabatans')->cascadeOnDelete();
            $table->string('nama');
            $table->string('nickname');
            $table->boolean('status');
            foreach (['gaji_pokok', 'tunjangan_jabatan', 'tunjangan_keluarga', 'nik', 'npwp', 'bpjs_tk', 'bpjs_kesehatan', 'tempat_lahir', 'tanggal_lahir', 'alamat', 'no_hp', 'bank', 'no_rek', 'nama_rek', 'mulai_bekerja', 'foto_ktp', 'foto_diri'] as $column) {
                $table->string($column)->nullable();
            }
            $table->boolean('apa_bpjs_tk')->default(false);
            $table->boolean('apa_bpjs_kes')->default(false);
            $table->timestamps();
        });
    }

    public function test_staff_defaults_to_active_and_all_statuses_remain_available(): void
    {
        $position = Jabatan::create(['nama' => 'Sales']);
        foreach ([1, 0] as $status) {
            Karyawan::create(['nomor' => $status + 1, 'jabatan_id' => $position->id, 'nama' => '<b>Pegawai</b>', 'nickname' => 'IUP', 'bank' => 'Bank uji', 'no_rek' => '001234', 'nama_rek' => '<b>Pemilik</b>', 'status' => $status]);
        }
        $controller = app(DatabaseController::class);
        $request = Request::create('/db/staff', 'GET', ['draw' => 1, 'start' => 0, 'length' => 10], [], [], ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);
        DB::flushQueryLog();
        DB::enableQueryLog();
        $active = $controller->staff($request)->getData(true);
        $countQueries = collect(DB::getQueryLog())->filter(fn (array $query): bool => str_contains(strtolower($query['query']), 'count(*)'));
        DB::disableQueryLog();
        $this->assertCount(1, $countQueries);
        $this->assertSame(1, $active['recordsTotal']);
        $this->assertSame('&lt;b&gt;Pegawai&lt;/b&gt;', $active['data'][0][1]);
        $this->assertStringContainsString('Bank uji', $active['data'][0][4]);
        $this->assertStringContainsString('001234', $active['data'][0][4]);
        $this->assertStringContainsString('&lt;b&gt;Pemilik&lt;/b&gt;', $active['data'][0][4]);
        $request->merge(['status' => 'all']);
        $this->assertSame(2, $controller->staff($request)->getData(true)['recordsTotal']);
        $request->merge(['search' => ['value' => 'Tidak ditemukan']]);
        $this->assertSame(0, $controller->staff($request)->getData(true)['recordsFiltered']);
    }

    public function test_used_position_cannot_cascade_delete_employee(): void
    {
        $position = Jabatan::create(['nama' => 'Staff']);
        $employee = Karyawan::create(['nomor' => 1, 'jabatan_id' => $position->id, 'nama' => 'Pegawai', 'nickname' => 'Staff', 'status' => 1]);
        $response = app(DatabaseController::class)->jabatan_delete($position);
        $this->assertSame('Jabatan masih digunakan pegawai dan tidak dapat dihapus.', $response->getSession()->get('error'));
        $this->assertDatabaseHas('karyawans', ['id' => $employee->id]);
        $this->assertDatabaseHas('jabatans', ['id' => $position->id]);
    }

    public function test_create_and_update_preserve_disabled_bpjs_and_existing_photos(): void
    {
        Storage::fake('local');
        $position = Jabatan::create(['nama' => 'Staff']);
        $data = ['jabatan_id' => $position->id, 'nama' => 'Pegawai uji', 'nickname' => 'Uji', 'status' => '1', 'mulai_bekerja' => '2026-01-01', 'tanggal_lahir' => '1990-01-01', 'gaji_pokok' => '1.500.000', 'tunjangan_jabatan' => '0', 'tunjangan_keluarga' => '0', 'apa_bpjs_tk' => '0', 'apa_bpjs_kes' => '0'];
        foreach (['nik', 'npwp', 'bpjs_tk', 'bpjs_kesehatan', 'tempat_lahir', 'alamat', 'no_hp', 'bank', 'no_rek', 'nama_rek'] as $field) {
            $data[$field] = 'Data uji';
        }
        $request = Request::create('/db/staff/store', 'POST', $data, [], ['foto_ktp' => UploadedFile::fake()->image('ktp.png'), 'foto_diri' => UploadedFile::fake()->image('diri.png')]);
        app(DatabaseController::class)->staff_store($request);
        $employee = Karyawan::firstOrFail();
        $this->assertSame(0, (int) $employee->apa_bpjs_tk);
        $this->assertSame(0, (int) $employee->apa_bpjs_kes);
        $this->assertSame(1500000, (int) $employee->gaji_pokok);
        $oldPhoto = $employee->foto_diri;
        $data['nama'] = 'Nama diperbarui';
        app(DatabaseController::class)->staff_update($employee, Request::create('/db/staff/update/'.$employee->id, 'PATCH', $data));
        $this->assertSame('Nama diperbarui', $employee->fresh()->nama);
        $this->assertSame($oldPhoto, $employee->fresh()->foto_diri);
        Storage::disk('local')->assertExists($oldPhoto);
    }

    public function test_status_changes_preserve_employee_and_related_data(): void
    {
        $position = Jabatan::create(['nama' => 'Staff']);
        $employee = Karyawan::create(['nomor' => 1, 'jabatan_id' => $position->id, 'nama' => 'Pegawai', 'nickname' => 'Staff', 'status' => 1]);
        $controller = app(DatabaseController::class);
        $controller->staff_status(Request::create('/', 'PATCH', ['status' => '0']), $employee);
        $this->assertSame(0, (int) $employee->fresh()->status);
        $this->assertDatabaseHas('jabatans', ['id' => $position->id]);
        $controller->staff_status(Request::create('/', 'PATCH', ['status' => '1']), $employee);
        $this->assertSame(1, (int) $employee->fresh()->status);
    }

    public function test_deletion_is_blocked_for_linked_employee_but_allowed_for_input_error(): void
    {
        Schema::create('employee_links', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawans')->nullOnDelete()->nullable();
        });
        $position = Jabatan::create(['nama' => 'Staff']);
        $employee = Karyawan::create(['nomor' => 1, 'jabatan_id' => $position->id, 'nama' => 'Pegawai', 'nickname' => 'Staff', 'status' => 1]);
        DB::table('employee_links')->insert(['karyawan_id' => $employee->id]);
        $request = Request::create('/', 'DELETE', ['deletion_reason' => 'Salah input pegawai']);
        $response = app(DatabaseController::class)->staff_delete($request, $employee);
        $this->assertNotEmpty($response->getSession()->get('error'));
        $this->assertDatabaseHas('karyawans', ['id' => $employee->id]);
        $this->assertDatabaseHas('employee_links', ['karyawan_id' => $employee->id]);
        DB::table('employee_links')->delete();
        app(DatabaseController::class)->staff_delete($request, $employee);
        $this->assertDatabaseMissing('karyawans', ['id' => $employee->id]);
    }

    public function test_nonactive_employee_existing_web_session_is_rejected(): void
    {
        $position = Jabatan::create(['nama' => 'Staff']);
        $employee = Karyawan::create(['nomor' => 1, 'jabatan_id' => $position->id, 'nama' => 'Pegawai', 'nickname' => 'Staff', 'status' => 0]);
        $user = new User(['name' => 'Sales', 'karyawan_id' => $employee->id]);
        $user->id = 999;
        $this->actingAs($user);
        $request = Request::create('/home');
        $request->setUserResolver(fn () => $user);
        $this->expectException(ValidationException::class);
        app(Authenticate::class)->handle($request, fn () => response('Allowed'));
    }

    private function prepareConnector(): AttendanceConnector
    {
        (require database_path('migrations/2026_10_16_202122_create_attendance_employee_syncs_table.php'))->up();
        config(['attendance.enabled' => true, 'attendance.url' => 'https://attendance.test', 'attendance.token' => str_repeat('x', 64), 'attendance.send_immediately' => false]);
        Http::preventStrayRequests();

        return app(AttendanceConnector::class);
    }

    public function test_connector_captures_minimal_identity_and_numeric_status(): void
    {
        $connector = $this->prepareConnector();
        $position = Jabatan::create(['nama' => 'Sales']);
        $employee = Karyawan::create(['nomor' => 7, 'jabatan_id' => $position->id, 'nama' => 'Pegawai', 'nickname' => 'Staff', 'status' => 1, 'gaji_pokok' => 5000000]);
        $sync = AttendanceEmployeeSync::sole();
        $this->assertSame('active', $sync->payload['status']);
        $this->assertSame(['source_employee_id', 'employee_number', 'name', 'position', 'status', 'employment_started_at'], array_keys($sync->payload));
        $employee->update(['gaji_pokok' => 6000000]);
        $this->assertSame(1, $sync->fresh()->source_version);
        $employee->update(['status' => 0]);
        $this->assertSame('inactive', $sync->fresh()->payload['status']);
        $this->assertSame(2, $sync->fresh()->source_version);
        $connector->reconcile();
        $this->assertSame(2, $sync->fresh()->source_version);
        Http::assertNothingSent();
    }

    public function test_connector_retries_and_requires_matching_acknowledgment(): void
    {
        $connector = $this->prepareConnector();
        $position = Jabatan::create(['nama' => 'Sales']);
        Karyawan::create(['nomor' => 7, 'jabatan_id' => $position->id, 'nama' => 'Pegawai', 'nickname' => 'Staff', 'status' => 1]);
        $sync = AttendanceEmployeeSync::sole();
        Http::fake(['attendance.test/*' => Http::sequence()->push([], 500)->push(['applied_version' => 1, 'data' => ['source_employee_id' => $sync->source_employee_id]])]);
        $this->assertFalse($connector->send($sync->id));
        $this->assertFalse($connector->send($sync->id));
        $this->assertSame(0, $sync->fresh()->delivered_version);
        $this->travel(16)->seconds();
        $this->assertTrue($connector->send($sync->id));
        $this->assertSame(1, $sync->fresh()->delivered_version);
        $this->assertNotNull(Karyawan::firstOrFail()->deletionBlockReason());
        $this->assertFalse($connector->send($sync->id));
        Http::assertSentCount(2);
    }

    public function test_connector_and_identity_change_roll_back_together(): void
    {
        $this->prepareConnector();
        $position = Jabatan::create(['nama' => 'Sales']);
        $employee = Karyawan::create(['nomor' => 7, 'jabatan_id' => $position->id, 'nama' => 'Semula', 'nickname' => 'Staff', 'status' => 1]);
        DB::beginTransaction();
        $employee->update(['nama' => 'Tidak jadi']);
        DB::rollBack();
        $this->assertSame('Semula', $employee->fresh()->nama);
        $this->assertSame('Semula', AttendanceEmployeeSync::sole()->payload['name']);
    }

    public function test_connector_page_rejects_non_admin_roles(): void
    {
        $this->prepareConnector();
        foreach (['sales', 'user', 'perusahaan', 'asisten-admin'] as $role) {
            $user = new User(['name' => 'Penguji', 'role' => $role]);
            $user->id = 999;
            $this->actingAs($user);
            $this->getJson('/db/staff/attendance-integration')->assertForbidden();
            $this->postJson('/db/staff/attendance-integration/sync')->assertForbidden();
        }
        Http::assertNothingSent();
    }

    protected function tearDown(): void
    {
        DB::purge('employee_crud_test');
        parent::tearDown();
    }
}
