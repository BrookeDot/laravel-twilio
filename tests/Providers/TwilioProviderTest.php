<?php

namespace BabDev\Twilio\Tests\Providers;

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

    protected function getPackageProviders($app)
    {
        return [TwilioProvider::class];
    }
}
