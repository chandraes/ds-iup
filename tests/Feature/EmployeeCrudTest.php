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
            $table->decimal('persen_komisi', 5, 2)->nullable();
            $table->unsignedInteger('tunjangan_bbm')->nullable();
            foreach (['gaji_pokok', 'tunjangan_jabatan', 'tunjangan_keluarga', 'nik', 'npwp', 'bpjs_tk', 'bpjs_kesehatan', 'tempat_lahir', 'tanggal_lahir', 'alamat', 'no_hp', 'bank', 'no_rek', 'nama_rek', 'mulai_bekerja', 'foto_ktp', 'foto_diri', 'perjanjian_kerja'] as $column) {
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

    public function test_staff_position_filter_combines_with_status_and_search(): void
    {
        $sales = Jabatan::create(['nama' => 'Sales']);
        $admin = Jabatan::create(['nama' => 'Admin']);
        foreach ([[$sales, 1, 'Sales aktif'], [$sales, 0, 'Sales lama'], [$admin, 1, 'Admin aktif']] as $index => [$position, $status, $name]) {
            Karyawan::create(['nomor' => $index + 1, 'jabatan_id' => $position->id, 'nama' => $name, 'nickname' => 'Uji', 'status' => $status]);
        }
        $user = new User(['name' => 'Admin', 'role' => 'admin']);
        $user->id = 999;
        $this->actingAs($user);
        $params = ['draw' => 1, 'start' => 0, 'length' => 25, 'jabatan_id' => $sales->id];
        $headers = ['X-Requested-With' => 'XMLHttpRequest'];
        $this->getJson('/db/staff?'.http_build_query($params), $headers)->assertOk()->assertJsonPath('recordsTotal', 1)->assertJsonPath('data.0.1', 'Sales aktif');
        $params['status'] = 'all';
        $this->getJson('/db/staff?'.http_build_query($params), $headers)->assertJsonPath('recordsTotal', 2);
        $params['search'] = ['value' => 'lama'];
        $this->getJson('/db/staff?'.http_build_query($params), $headers)->assertJsonPath('recordsFiltered', 1)->assertJsonPath('data.0.1', 'Sales lama');
        $params['jabatan_id'] = $admin->id;
        $this->getJson('/db/staff?'.http_build_query($params), $headers)->assertJsonPath('recordsFiltered', 0);
        $this->getJson('/db/staff?jabatan_id=999999', $headers)->assertUnprocessable()->assertJsonValidationErrors('jabatan_id');
        $this->get('/db/staff?jabatan_id='.$sales->id)->assertOk()->assertViewHas('selectedJabatan', (string) $sales->id)->assertSee('Semua jabatan');
    }

    public function test_sales_position_cannot_be_deleted_or_renamed_even_without_employees(): void
    {
        $user = new User(['name' => 'Admin', 'role' => 'admin']);
        $user->id = 999;
        $this->actingAs($user);
        $sales = Jabatan::create(['nama' => ' SALES ']);
        $this->delete(route('db.staff.jabatan.delete', $sales))->assertSessionHas('error', 'Jabatan Sales merupakan jabatan sistem dan tidak dapat dihapus.');
        $this->assertModelExists($sales);
        $this->patch(route('db.staff.jabatan.update', $sales), ['nama' => 'Lain'])->assertSessionHas('error');
        $this->assertSame(' SALES ', $sales->fresh()->nama);
        $legacy = Jabatan::forceCreate(['id' => 3, 'nama' => 'Nama lama']);
        $this->delete(route('db.staff.jabatan.delete', $legacy))->assertSessionHas('error');
        $this->assertModelExists($legacy);
        $ordinary = Jabatan::create(['nama' => 'Jabatan uji']);
        $this->delete(route('db.staff.jabatan.delete', $ordinary))->assertRedirect();
        $this->assertModelMissing($ordinary);
        $this->get(route('db.staff'))->assertOk()->assertSee('Nama dan penghapusan Sales dikunci.');
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
        $request = Request::create('/db/staff/store', 'POST', $data, [], ['perjanjian_kerja' => UploadedFile::fake()->create('kontrak.pdf', 5120, 'application/pdf'), 'foto_ktp' => UploadedFile::fake()->image('ktp.png'), 'foto_diri' => UploadedFile::fake()->image('diri.png')]);
        app(DatabaseController::class)->staff_store($request);
        $employee = Karyawan::firstOrFail();
        $this->assertSame(0, (int) $employee->apa_bpjs_tk);
        $this->assertSame(0, (int) $employee->apa_bpjs_kes);
        $this->assertSame(1500000, (int) $employee->gaji_pokok);
        $oldAgreement = $employee->perjanjian_kerja;
        Storage::disk('local')->assertExists($oldAgreement);
        $oldPhoto = $employee->foto_diri;
        $data['nama'] = 'Nama diperbarui';
        app(DatabaseController::class)->staff_update($employee, Request::create('/db/staff/update/'.$employee->id, 'PATCH', $data));
        $this->assertSame('Nama diperbarui', $employee->fresh()->nama);
        $this->assertSame($oldPhoto, $employee->fresh()->foto_diri);
        Storage::disk('local')->assertExists($oldPhoto);
        $this->assertSame($oldAgreement, $employee->fresh()->perjanjian_kerja);
        $replacement = Request::create('/db/staff/update/'.$employee->id, 'POST', $data, [], ['perjanjian_kerja' => UploadedFile::fake()->create('baru.pdf', 50, 'application/pdf')]);
        app(DatabaseController::class)->staff_update($employee, $replacement);
        Storage::disk('local')->assertMissing($oldAgreement);
        Storage::disk('local')->assertExists($employee->fresh()->perjanjian_kerja);
        app(DatabaseController::class)->staff_delete(Request::create('/', 'DELETE', ['deletion_reason' => 'Salah input pegawai']), $employee);
        Storage::disk('local')->assertMissing($employee->perjanjian_kerja);
    }

    public function test_agreement_rejects_invalid_type_extension_and_size(): void
    {
        $user = new User(['name' => 'Admin', 'role' => 'admin']);
        $user->id = 999;
        $this->actingAs($user);
        foreach ([UploadedFile::fake()->image('foto.png'), UploadedFile::fake()->create('kontrak.txt', 10, 'application/pdf'), UploadedFile::fake()->create('kontrak.pdf', 5121, 'application/pdf')] as $file) {
            $this->post('/db/staff/store', ['perjanjian_kerja' => $file])->assertSessionHasErrors('perjanjian_kerja');
        }
    }

    public function test_employee_documents_require_admin_and_existing_allowed_document(): void
    {
        Storage::fake('local');
        $position = Jabatan::create(['nama' => 'Staff']);
        $employee = Karyawan::create(['nomor' => 1, 'jabatan_id' => $position->id, 'nama' => 'Pegawai', 'nickname' => 'Staff', 'status' => 1, 'perjanjian_kerja' => 'karyawan/perjanjian-kerja/test.pdf']);
        Storage::disk('local')->put($employee->perjanjian_kerja, '%PDF-1.4 test');
        $url = route('db.staff.document', [$employee, 'perjanjian_kerja']);
        $this->get($url)->assertRedirect('/login');
        $user = new User(['name' => 'Penguji', 'role' => 'sales']);
        $user->id = 999;
        $this->actingAs($user)->get($url)->assertForbidden();
        $user->role = 'admin';
        $this->actingAs($user)->get($url)->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->get(route('db.staff.document', [$employee, 'foto_ktp']))->assertNotFound();
        $this->get(route('db.staff.document', [$employee, 'nik']))->assertNotFound();
        $response = $this->getJson('/db/staff?draw=1&start=0&length=25', ['X-Requested-With' => 'XMLHttpRequest']);
        $response->assertOk();
        $this->assertStringContainsString($url, $response->json('data.0.6'));
        Storage::disk('local')->assertExists($employee->perjanjian_kerja);
    }

    public function test_staff_detail_displays_employee_data_and_documents_for_admin_only(): void
    {
        $position = Jabatan::create(['nama' => 'Supervisor']);
        $employee = Karyawan::create([
            'nomor' => 17, 'jabatan_id' => $position->id, 'nama' => '<b>Pegawai detail</b>', 'nickname' => 'Panggilan uji', 'status' => 0,
            'gaji_pokok' => 1500000, 'tunjangan_jabatan' => 200000, 'tunjangan_keluarga' => 100000,
            'nik' => '0012345678901234', 'npwp' => '00987654321', 'bpjs_tk' => 'TK123', 'bpjs_kesehatan' => 'KES123',
            'apa_bpjs_tk' => 1, 'apa_bpjs_kes' => 0, 'tempat_lahir' => 'Palembang', 'tanggal_lahir' => '1990-02-03',
            'alamat' => 'Alamat pegawai uji', 'no_hp' => '081234567890', 'bank' => 'Bank uji', 'no_rek' => '00123456',
            'nama_rek' => 'Pemilik rekening', 'mulai_bekerja' => '2026-01-02', 'foto_ktp' => 'public/karyawan/ktp.png',
            'foto_diri' => 'public/karyawan/foto.png', 'perjanjian_kerja' => 'karyawan/perjanjian-kerja/kontrak.pdf',
        ]);
        $url = route('db.staff.show', $employee);
        $this->get($url)->assertRedirect('/login');
        $user = new User(['name' => 'Penguji', 'role' => 'sales']);
        $user->id = 999;
        $this->actingAs($user)->get($url)->assertForbidden();
        $user->role = 'admin';
        $response = $this->actingAs($user)->get($url);
        $response->assertOk()->assertViewIs('db.karyawan.show');
        foreach (['<b>Pegawai detail</b>', 'Panggilan uji', 'Supervisor', 'Nonaktif', 'Rp 1.500.000', 'Rp 200.000', 'Rp 100.000', '0012345678901234', '00987654321', 'TK123', 'KES123', 'Ya', 'Tidak', 'Palembang', '03-02-1990', 'Alamat pegawai uji', '081234567890', 'Bank uji', '00123456', 'Pemilik rekening', '02-01-2026'] as $value) {
            $response->assertSee($value);
        }
        $response->assertDontSee('<b>Pegawai detail</b>', false);
        $response->assertDontSee('Pencatatan data')->assertDontSee('Dibuat pada')->assertDontSee('Diperbarui pada');
        foreach (['foto_ktp', 'foto_diri', 'perjanjian_kerja'] as $document) {
            $response->assertSee(route('db.staff.document', [$employee, $document]));
        }
        $list = $this->getJson('/db/staff?status=all&draw=1&start=0&length=25', ['X-Requested-With' => 'XMLHttpRequest']);
        $this->assertStringContainsString($url, $list->json('data.0.7'));
        $this->get(route('db.staff.show', 999999))->assertNotFound();
        $employee->update(['foto_ktp' => null, 'foto_diri' => null, 'perjanjian_kerja' => null]);
        $this->get($url)->assertOk()->assertSee('Perjanjian kerja belum diunggah.')->assertSee('Belum ada foto diri.')->assertSee('Belum ada foto ktp.');
    }

    public function test_sales_compensation_is_required_validated_and_saved_on_create_and_update(): void
    {
        Storage::fake('local');
        $sales = Jabatan::create(['nama' => 'SALES']);
        $staff = Jabatan::create(['nama' => 'Admin']);
        $user = new User(['name' => 'Admin', 'role' => 'admin']);
        $user->id = 999;
        $this->actingAs($user);
        $data = ['jabatan_id' => $sales->id, 'nama' => 'Sales uji', 'nickname' => 'Uji', 'status' => 1, 'mulai_bekerja' => '2026-01-01', 'tanggal_lahir' => '1990-01-01', 'gaji_pokok' => 1000000, 'tunjangan_jabatan' => 0, 'tunjangan_keluarga' => 0];
        foreach (['nik', 'npwp', 'bpjs_tk', 'bpjs_kesehatan', 'tempat_lahir', 'alamat', 'no_hp', 'bank', 'no_rek', 'nama_rek'] as $field) {
            $data[$field] = 'Data uji';
        }
        $this->post('/db/staff/store', $data)->assertSessionHasErrors(['persen_komisi', 'tunjangan_bbm']);
        $data += ['persen_komisi' => '4.25', 'tunjangan_bbm' => '15.000'];
        $this->post('/db/staff/store', $data + ['foto_ktp' => UploadedFile::fake()->image('ktp.png'), 'foto_diri' => UploadedFile::fake()->image('diri.png')])->assertRedirect(route('db.staff'));
        $employee = Karyawan::sole();
        $this->assertEquals(4.25, $employee->persen_komisi);
        $this->assertEquals(15000, $employee->tunjangan_bbm);
        $url = route('db.staff.update', $employee);
        $missing = $data;
        unset($missing['persen_komisi'], $missing['tunjangan_bbm']);
        $this->patch($url, $missing)->assertSessionHasErrors(['persen_komisi', 'tunjangan_bbm']);
        foreach ([['persen_komisi' => -1], ['persen_komisi' => 101], ['persen_komisi' => '4.123'], ['tunjangan_bbm' => -1], ['tunjangan_bbm' => 'abc']] as $invalid) {
            $this->patch($url, array_replace($data, $invalid))->assertSessionHasErrors(array_keys($invalid));
        }
        $this->patch($url, array_replace($data, ['persen_komisi' => 5, 'tunjangan_bbm' => '20.000']))->assertRedirect(route('db.staff'));
        $this->assertEquals(5, $employee->fresh()->persen_komisi);
        $this->assertEquals(20000, $employee->fresh()->tunjangan_bbm);
        $this->get(route('db.staff.show', $employee))->assertOk()->assertSee('5 %')->assertSee('Rp 20.000');
        $this->get(route('db.staff.edit', $employee))->assertOk()->assertSee('Persentase komisi (%)');
        $this->patch($url, array_replace($missing, ['jabatan_id' => $staff->id]))->assertRedirect(route('db.staff'));
        $this->assertNull($employee->fresh()->persen_komisi);
        $this->assertNull($employee->fresh()->tunjangan_bbm);
        $this->get(route('db.staff.show', $employee))->assertDontSee('Persentase komisi');
    }

    public function test_existing_sales_receive_initial_compensation_only_for_sales_positions(): void
    {
        $sales = Jabatan::create(['nama' => ' SALES ']);
        $staff = Jabatan::create(['nama' => 'Admin']);
        foreach ([$sales, $staff] as $position) {
            Karyawan::create(['nomor' => $position->id, 'jabatan_id' => $position->id, 'nama' => 'Pegawai', 'nickname' => 'Uji', 'status' => 1]);
        }
        (require database_path('migrations/2026_10_23_074222_backfill_sales_compensation_for_existing_employees.php'))->up();
        $employee = Karyawan::where('jabatan_id', $sales->id)->firstOrFail();
        $this->assertEquals(4, $employee->persen_komisi);
        $this->assertEquals(15000, $employee->tunjangan_bbm);
        $this->assertNull(Karyawan::where('jabatan_id', $staff->id)->firstOrFail()->persen_komisi);
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
