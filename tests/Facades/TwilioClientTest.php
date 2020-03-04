<?php

namespace BabDev\Twilio\Tests\Facades;

use BabDev\Twilio\ConnectionManager;
use BabDev\Twilio\Contracts\TwilioClient as TwilioClientContract;
use BabDev\Twilio\Facades\TwilioClient as TwilioClientFacade;
use BabDev\Twilio\Providers\TwilioProvider;
use BabDev\Twilio\TwilioClient;
use Illuminate\Contracts\Container\Container;
use Orchestra\Testbench\TestCase;
use Twilio\Rest\Api\V2010\Account\CallInstance;
use Twilio\Rest\Api\V2010\Account\MessageInstance;
use Twilio\Rest\Client;

final class TwilioClientTest extends TestCase
{
    public function testTheDefaultConnectionIsCreated(): void
    {
        $this->assertInstanceOf(TwilioClient::class, \TwilioClient::connection());
    }

    public function testACustomConnectionIsCreated(): void
    {
        $this->assertInstanceOf(TwilioClient::class, \TwilioClient::connection('custom'));
        $this->assertNotSame(\TwilioClient::connection(), \TwilioClient::connection('custom'), 'The default manager instance should not be the same as the custom instance.');
    }

    public function testAnUnknownCustomConnectionCausesAnException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        \TwilioClient::connection('does_not_exist');
    }

    public function testRetrievingTheSdkClientProxiesThrough(): void
    {
        $twilioClient = $this->createMock(Client::class);

        $client = $this->createMock(TwilioClientContract::class);
        $client->expects($this->once())
            ->method('twilio')
            ->willReturn($twilioClient);

        /** @var ConnectionManager $manager */
        $manager = $this->app->make(ConnectionManager::class);
        $manager->extend(
            'twilio',
            function (Container $container) use ($client): TwilioClientContract {
                return $client;
            }
        );

        $this->assertSame($twilioClient, \TwilioClient::twilio());
    }

    public function testPlacingACallProxiesThrough(): void
    {
        $call = $this->createMock(CallInstance::class);

        $client = $this->createMock(TwilioClientContract::class);
        $client->expects($this->once())
            ->method('call')
            ->willReturn($call);

        /** @var ConnectionManager $manager */
        $manager = $this->app->make(ConnectionManager::class);
        $manager->extend(
            'twilio',
            function (Container $container) use ($client): TwilioClientContract {
                return $client;
            }
        );

        $this->assertSame($call, \TwilioClient::call('me', []));
    }

    public function testSendingAMessageProxiesThrough(): void
    {
        $message = $this->createMock(MessageInstance::class);

        $client = $this->createMock(TwilioClientContract::class);
        $client->expects($this->once())
            ->method('message')
            ->willReturn($message);

        /** @var ConnectionManager $manager */
        $manager = $this->app->make(ConnectionManager::class);

        $manager->extend(
            'twilio',
            function (Container $container) use ($client): TwilioClientContract {
                return $client;
            }
        );

        $this->assertSame($message, \TwilioClient::message('me', 'Hello!', []));
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup connections configuration
        $app['config']->set(
            'twilio.connections.twilio',
            [
                'sid' => 'api_sid',
                'token' => 'api_token',
                'from' => '+18675309',
            ]
        );

        $app['config']->set(
            'twilio.connections.custom',
            [
                'sid' => 'custom_sid',
                'token' => 'custom_token',
                'from' => '+18675309',
            ]
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            TwilioProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'TwilioClient' => TwilioClientFacade::class,
        ];
    }
}
