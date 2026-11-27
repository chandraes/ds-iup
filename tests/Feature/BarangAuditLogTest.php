<?php

namespace Tests\Feature;

use App\Models\db\Barang\BarangAuditLog;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BarangAuditLogTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['database.default' => 'barang_audit_test', 'database.connections.barang_audit_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        (require database_path('migrations/2026_11_27_190500_create_barang_audit_logs_table.php'))->up();
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });
        Schema::create('barang_namas', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
        });
        Schema::create('barangs', function (Blueprint $table): void {
            $table->id();
            $table->integer('barang_nama_id')->default(1);
            $table->string('kode')->default('KODE');
            $table->string('merk')->default('Merk');
            $table->string('foto')->nullable();
            $table->decimal('diskon')->default(0);
            $table->date('diskon_mulai')->nullable();
            $table->date('diskon_selesai')->nullable();
            $table->timestamps();
        });
        Schema::create('barang_stok_hargas', function (Blueprint $table): void {
            $table->id();
            $table->integer('barang_id');
        });
        DB::table('barang_namas')->insert(['id' => 1, 'nama' => 'Semen']);
        DB::table('barangs')->insert([['id' => 1, 'foto' => 'barang/1.jpg'], ['id' => 2, 'foto' => 'barang/2.jpg']]);
        $this->actingAs(new User(['name' => 'Admin Uji', 'role' => 'admin']));
    }

    public function test_update_logs_only_changed_fields_of_the_target_barang_with_request_source(): void
    {
        $this->postJson(route('db.barang.diskon', 1), ['diskon' => 10, 'diskon_mulai' => '01-12-2026', 'diskon_selesai' => '10-12-2026'])
            ->assertJsonPath('status', 'success');

        $log = BarangAuditLog::sole();
        $this->assertSame(['updated', 1, 'Admin Uji', 'db.barang.diskon'], [$log->action, $log->barang_id, $log->user_name, $log->context['route']]);
        $this->assertEqualsCanonicalizing(['diskon', 'diskon_mulai', 'diskon_selesai'], array_keys($log->new_values));
        $this->assertEquals(0, $log->old_values['diskon']);
        $this->assertEquals(10, $log->context['input']['diskon']);
        $this->assertSame('barang/2.jpg', DB::table('barangs')->where('id', 2)->value('foto'));
    }

    public function test_barang_without_stock_is_deleted_and_barang_with_stock_is_rejected_with_reason(): void
    {
        DB::table('barang_stok_hargas')->insert(['barang_id' => 2]);

        $this->delete(route('db.barang.delete', 1))->assertSessionHas('success');
        $this->delete(route('db.barang.delete', 2))->assertSessionHas('error');

        $this->assertDatabaseMissing('barangs', ['id' => 1]);
        $this->assertDatabaseHas('barangs', ['id' => 2]);
        $this->assertSame('barang/1.jpg', BarangAuditLog::where('action', 'deleted')->sole()->old_values['foto']);
        $this->assertStringContainsString('1 data stok', BarangAuditLog::where('action', 'delete_rejected')->sole()->context['reason']);
    }

    public function test_audit_page_is_su_only_and_filters_by_barang_including_deleted_history(): void
    {
        $this->delete(route('db.barang.delete', 1));
        $this->get(route('db.barang.audit-log'))->assertForbidden();
        $this->actingAs(new User(['name' => 'Super User', 'role' => 'su']));
        $this->postJson(route('db.barang.diskon', 2), ['diskon' => 5, 'diskon_mulai' => '01-12-2026', 'diskon_selesai' => '10-12-2026']);

        $this->get(route('db.barang.audit-log', ['barang_id' => 1]))->assertOk()
            ->assertSee('sudah dihapus')->assertSee('Hapus')->assertDontSee('#2</a>', false);
        $this->get(route('db.barang.audit-log'))->assertOk()->assertSee('#2</a>', false);
    }
}
