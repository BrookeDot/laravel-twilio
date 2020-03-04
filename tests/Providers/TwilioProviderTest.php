<?php

namespace BabDev\Twilio\Tests\Providers;

use BabDev\Twilio\Providers\ConnectionManager;
use BabDev\Twilio\Providers\TwilioProvider;
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
        $this->assertTrue($this->app->bound('babdev.twilio.manager'));
        $this->assertSame('babdev.twilio.manager', $this->app->getAlias(ConnectionManager::class));
    }

    protected function getPackageProviders($app)
    {
        return [TwilioProvider::class];
    }
}
