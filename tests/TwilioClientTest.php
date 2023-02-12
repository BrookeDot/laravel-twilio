<?php

namespace BabDev\Twilio\Tests;

use BabDev\Twilio\Facades\TwilioClient;
use BabDev\Twilio\Providers\TwilioProvider;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;
use Orchestra\Testbench\TestCase;
use Twilio\Rest\Api\V2010\Account\CallInstance;
use Twilio\Rest\Api\V2010\Account\MessageInstance;
use Twilio\Rest\Client;

final class TwilioClientTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        // Setup connections configuration
        $app['config']->set(
            'twilio.connections.twilio',
            [
                'sid' => 'account-sid',
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

    public function testTheSdkInstanceCanBeRetrieved(): void
    {
        $this->assertInstanceOf(Client::class, TwilioClient::twilio());
    }

    public function testACallCanBeCreated(): void
    {
        $to = '+15558675309';

        Http::fake([
            'https://api.twilio.com/2010-04-01/Accounts/account-sid/Calls.json' => Http::response(
                $this->getMessageSentResponseContent(config('twilio.connections.twilio.from'), $to),
                201,
            ),
        ]);

        $this->assertInstanceOf(CallInstance::class, TwilioClient::call($to));
    }

    public function testACallCanBeCreatedWithACustomFromNumber(): void
    {
        $to = '+15558675309';
        $customFrom = '+16518675309';

        Http::fake([
            'https://api.twilio.com/2010-04-01/Accounts/account-sid/Calls.json' => Http::response(
                $this->getMessageSentResponseContent($customFrom, $to),
                201,
            ),
        ]);

        $this->assertInstanceOf(CallInstance::class, TwilioClient::call($to));
    }

    public function testAMessageCanBeSent(): void
    {
        $to = '+15558675309';
        $message = 'Test Message';

        Http::fake([
            'https://api.twilio.com/2010-04-01/Accounts/account-sid/Messages.json' => Http::response(
                $this->getMessageSentResponseContent(config('twilio.connections.twilio.from'), $to),
                201,
            ),
        ]);

        $this->assertInstanceOf(MessageInstance::class, TwilioClient::message($to, $message));
    }

    public function testAMessageCanBeSentWithACustomFromNumber(): void
    {
        $to = '+15558675309';
        $customFrom  = '+16518675309';
        $message = 'Test Message';

        Http::fake([
            'https://api.twilio.com/2010-04-01/Accounts/account-sid/Messages.json' => Http::response(
                $this->getMessageSentResponseContent($customFrom, $to),
                201,
            ),
        ]);

        $this->assertInstanceOf(MessageInstance::class, TwilioClient::message($to, $message, ['from' => $customFrom]));
    }

    /**
     * @return array<string, mixed>
     */
    private function getMessageSentResponseContent(string $from, string $to): array
    {
        $date = Date::now()->toRfc822String();

        return [
            'body'                  => 'Test',
            'num_segments'          => '1',
            'direction'             => 'outbound-api',
            'from'                  => $from,
            'date_updated'          => $date,
            'price'                 => null,
            'error_message'         => null,
            'uri'                   => '/2010-04-01/Accounts/account-sid/Messages/message-sid.json',
            'account_sid'           => 'account-sid',
            'num_media'             => '0',
            'to'                    => $to,
            'date_created'          => $date,
            'status'                => 'queued',
            'sid'                   => 'message-sid',
            'date_sent'             => null,
            'messaging_service_sid' => null,
            'error_code'            => null,
            'price_unit'            => 'USD',
            'api_version'           => '2010-04-01',
            'subresource_uris'      => [
                'media' => '/2010-04-01/Accounts/account-sid/Messages/message-sid/Media.json',
            ],
        ];
    }
}
