<?php

namespace BabDev\Twilio\Tests\Providers;

use BabDev\Twilio\ConnectionManager;
use BabDev\Twilio\Contracts\TwilioClient;
use BabDev\Twilio\Notifications\Channels\TwilioChannel;
use BabDev\Twilio\Providers\TwilioProvider;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\ServiceProvider;
use Orchestra\Testbench\TestCase;

final class TwilioProviderTest extends TestCase
{
    public function testServiceProviderPublishesConfiguration(): void
    {
        $this->assertArrayHasKey(
            TwilioProvider::class,
            ServiceProvider::$publishes,
            'The service provider should be publishing its configuration.'
        );
    }

    public function testServicesAreRegistered(): void
    {
        $this->assertTrue($this->app->bound(ConnectionManager::class));
        $this->assertSame(ConnectionManager::class, $this->app->getAlias(TwilioClient::class));
        $this->assertInstanceOf(TwilioChannel::class, $this->app->get(ChannelManager::class)->driver('twilio'));
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Setup connections configuration
        $app['config']->set(
            'twilio.connections.twilio',
            [
                'sid' => 'api_sid',
                'token' => 'api_token',
                'from' => '+15558675309',
            ]
        );
    }

    /**
     * @return class-string<ServiceProvider>
     */
    protected function getPackageProviders($app): array
    {
        return [
            TwilioProvider::class,
        ];
    }
}
