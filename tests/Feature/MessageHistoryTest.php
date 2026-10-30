<?php

namespace Tests\Feature;

use App\Models\PesanWa;
use App\Models\User;
use App\Services\StarSender;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class MessageHistoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['database.default' => 'history_test', 'database.connections.history_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        Schema::create('pesan_was', function (Blueprint $table): void {
            $table->id();
            $table->text('pesan');
            $table->string('tujuan');
            $table->boolean('status');
            $table->timestamps();
        });
        (require glob(database_path('migrations/*_add_last_attempt_at_to_pesan_was_table.php'))[0])->up();
        Schema::create('group_was', function (Blueprint $table): void {
            $table->id();
            $table->string('nama_group');
            $table->string('group_id');
        });
        $user = new User(['name' => 'Admin uji', 'role' => 'admin']);
        $user->id = 900;
        $this->actingAs($user);
        $this->travelTo(now()->setDate(2026, 10, 30)->setTime(14, 0));
    }

    public function test_history_returns_both_timestamps_and_keeps_server_pagination(): void
    {
        foreach (range(1, 105) as $id) {
            DB::table('pesan_was')->insert(['id' => $id, 'pesan' => 'Pesan uji '.$id, 'tujuan' => 'group-test', 'status' => 0,
                'created_at' => '2026-10-30 09:00:00', 'updated_at' => '2026-10-30 09:00:00', 'last_attempt_at' => $id === 15 ? null : '2026-10-30 10:00:00']);
        }
        $this->getJson(route('histori-pesan', ['draw' => 1, 'start' => 90, 'length' => 10]), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertJsonPath('recordsTotal', 105)->assertJsonCount(10, 'data')
            ->assertJsonPath('data.0.DT_RowIndex', 91)->assertJsonPath('data.0.id', 15)->assertJsonPath('data.0.created_at_display', '30-10-2026 09:00:00 WIB')
            ->assertJsonPath('data.0.last_attempt_at_display', 'Belum tercatat')
            ->assertJsonPath('data.1.last_attempt_at_display', '30-10-2026 10:00:00 WIB');
    }

    #[TestWith([true])]
    #[TestWith([false])]
    public function test_ajax_resend_records_attempt_time_on_success_or_failure_and_preserves_creation_time(bool $sent): void
    {
        $message = PesanWa::create(['pesan' => 'Pesan uji', 'tujuan' => 'group-test', 'status' => 0]);
        $created = $message->created_at->toDateTimeString();
        $this->travel(1)->hours();
        $mock = Mockery::mock(StarSender::class);
        $mock->shouldReceive('sendGroup')->once()->andReturn($sent);
        $this->app->bind(StarSender::class, fn () => $mock);
        $response = $this->postJson(route('histori.resend', $message));
        $response->assertStatus($sent ? 200 : 422)->assertJsonPath('success', $sent);
        $message->refresh();
        $this->assertSame($created, $message->created_at->toDateTimeString());
        $this->assertSame(now()->toDateTimeString(), $message->last_attempt_at->toDateTimeString());
        $this->assertSame($sent ? 1 : 0, (int) $message->status);
    }

    public function test_already_sent_message_and_concurrent_resend_never_send_again(): void
    {
        $message = PesanWa::create(['pesan' => 'Pesan uji', 'tujuan' => 'group-test', 'status' => 1]);
        $mock = Mockery::mock(StarSender::class);
        $mock->shouldNotReceive('sendGroup');
        $this->app->bind(StarSender::class, fn () => $mock);
        $this->postJson(route('histori.resend', $message))->assertOk()->assertJsonPath('success', true);
        $message->update(['status' => 0]);
        $lock = Cache::lock('whatsapp-resend:'.$message->id, 90);
        $lock->get();
        try {
            $this->postJson(route('histori.resend', $message))->assertConflict();
        } finally {
            $lock->release();
        }
        $this->assertNull($message->fresh()->last_attempt_at);
    }

    public function test_sales_cannot_read_history_or_resend(): void
    {
        $message = PesanWa::create(['pesan' => 'Pesan uji', 'tujuan' => 'group-test', 'status' => 0]);
        $sales = new User(['name' => 'Sales', 'role' => 'sales']);
        $sales->id = 901;
        $this->actingAs($sales)->getJson(route('histori-pesan'))->assertForbidden();
        $this->postJson(route('histori.resend', $message))->assertForbidden();
        $this->assertNull($message->fresh()->last_attempt_at);
    }

    public function test_provider_exception_records_attempt_without_marking_message_sent(): void
    {
        $message = PesanWa::create(['pesan' => 'Pesan uji', 'tujuan' => 'group-test', 'status' => 0]);
        $mock = Mockery::mock(StarSender::class);
        $mock->shouldReceive('sendGroup')->once()->andThrow(new \RuntimeException('Provider unavailable'));
        $this->app->bind(StarSender::class, fn () => $mock);
        $this->postJson(route('histori.resend', $message))->assertStatus(502)->assertJsonPath('success', false);
        $this->assertNotNull($message->fresh()->last_attempt_at);
        $this->assertSame(0, (int) $message->fresh()->status);
    }

    public function test_guest_cannot_read_or_resend(): void
    {
        $message = PesanWa::create(['pesan' => 'Pesan uji', 'tujuan' => 'group-test', 'status' => 0]);
        $this->app['auth']->forgetGuards();
        $this->getJson(route('histori-pesan'))->assertUnauthorized();
        $this->postJson(route('histori.resend', $message))->assertUnauthorized();
        $this->assertNull($message->fresh()->last_attempt_at);
    }
}
