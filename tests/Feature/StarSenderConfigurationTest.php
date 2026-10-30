<?php

namespace Tests\Feature;

use App\Services\StarSender;
use App\Services\WaStatus;
use Illuminate\Support\Facades\Http;
use ReflectionProperty;
use Tests\TestCase;

class StarSenderConfigurationTest extends TestCase
{
    public function test_sender_uses_configured_device_key_when_environment_is_not_loaded(): void
    {
        config(['services.starsender.device_key' => 'device-key-from-cached-config']);
        $sender = new StarSender('0000000000', 'Not sent');

        $this->assertTrue(hash_equals('device-key-from-cached-config', (string) (new ReflectionProperty(StarSender::class, 'apikey'))->getValue($sender)));
        Http::assertNothingSent();
    }

    public function test_device_status_and_groups_use_all_cached_configuration_credentials(): void
    {
        config(['services.starsender.device_key' => 'device-key-from-cached-config',
            'services.starsender.api_key' => 'account-key-from-cached-config',
            'services.starsender.device_id' => 'device-from-cached-config']);
        $status = new WaStatus;

        foreach (['apikey' => 'device-key-from-cached-config', 'apiDevKey' => 'account-key-from-cached-config', 'deviceId' => 'device-from-cached-config'] as $property => $expected) {
            $this->assertTrue(hash_equals($expected, (string) (new ReflectionProperty(WaStatus::class, $property))->getValue($status)));
        }
        Http::assertNothingSent();
    }
}
