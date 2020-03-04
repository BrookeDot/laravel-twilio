<?php

namespace BabDev\Twilio\Tests\Twilio\Http;

use BabDev\Twilio\Twilio\Http\LaravelHttpClient;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;
use Orchestra\Testbench\TestCase;
use Twilio\Exceptions\HttpException;

final class LaravelHttpClientTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        if (!\class_exists(Factory::class)) {
            self::markTestSkipped('Test only applies to Laravel 7 or newer.');
        }

        parent::setUpBeforeClass();
    }

    public function testARequestCanBeSentToTheTwilioApiWithoutCredentials(): void
    {
        $url         = 'https://api.twilio.com/2010-04-01/Accounts/SID/Messages.json';
        $headers     = [];
        $messageData = [
            'From' => '+16512432364',
            'To'   => '+18003285920',
            'Body' => 'Test Message',
        ];

        /** @var Factory $factory */
        $factory = $this->app->make(Factory::class);
        $factory->fake(
            [
                $url => $factory->response('', 200, []),
            ]
        );

        (new LaravelHttpClient($factory))->request(
            'POST',
            $url,
            [],
            $messageData,
            $headers
        );

        $factory->assertSent(static function (Request $request, Response $response) use ($url, $messageData): bool {
            return !$request->hasHeader('Authorization')
                && $request->url() === $url
                && $request->data() === $messageData;
        });
    }

    public function testARequestCanBeSentToTheTwilioApiWithCredentials(): void
    {
        $url         = 'https://api.twilio.com/2010-04-01/Accounts/SID/Messages.json';
        $headers     = [];
        $messageData = [
            'From' => '+16512432364',
            'To'   => '+18003285920',
            'Body' => 'Test Message',
        ];

        /** @var Factory $factory */
        $factory = $this->app->make(Factory::class);
        $factory->fake(
            [
                $url => $factory->response('', 200, []),
            ]
        );

        (new LaravelHttpClient($factory))->request(
            'POST',
            $url,
            [],
            $messageData,
            $headers,
            'username',
            'password'
        );

        $factory->assertSent(static function (Request $request, Response $response) use ($url, $messageData): bool {
            return $request->hasHeader('Authorization')
                && $request->url() === $url
                && $request->data() === $messageData;
        });
    }

    public function testAnExceptionIsThrownWhenThereIsAnErrorPerformingTheRequest(): void
    {
        $this->expectException(HttpException::class);

        $url         = 'https://api.twilio.com/2010-04-01/Accounts/SID/Messages.json';
        $headers     = [];
        $messageData = [
            'From' => '+16512432364',
            'To'   => '+18003285920',
            'Body' => 'Test Message',
        ];

        /** @var Factory $factory */
        $factory = $this->app->make(Factory::class);
        $factory->fake(
            [
                $url => static function () {
                    throw new \RuntimeException('Testing');
                },
            ]
        );

        (new LaravelHttpClient($factory))->request(
            'POST',
            $url,
            [],
            $messageData,
            $headers,
            'username',
            'password'
        );
    }
}
