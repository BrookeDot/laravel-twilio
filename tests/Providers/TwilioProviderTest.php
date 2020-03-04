<?php

namespace BabDev\Twilio\Tests\Providers;

use BabDev\Twilio\Providers\TwilioProvider;
use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;

final class TwilioProviderTest extends TestCase
{
    public function testServiceIsRegistered(): void
    {
        $container = new Container();

        (new TwilioProvider($container))->register();
    }
}
