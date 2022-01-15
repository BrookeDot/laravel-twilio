<?php

namespace BabDev\Twilio\Tests;

use BabDev\Twilio\TwilioClient;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Twilio\Rest\Api\V2010\Account\CallInstance;
use Twilio\Rest\Api\V2010\Account\CallList;
use Twilio\Rest\Api\V2010\Account\MessageInstance;
use Twilio\Rest\Api\V2010\Account\MessageList;
use Twilio\Rest\Client;

final class TwilioClientTest extends TestCase
{
    public function testTheSdkInstanceCanBeRetrieved(): void
    {
        $defaultFrom = '+19418675309';

        /** @var MockObject&Client $twilio */
        $twilio = $this->createMock(Client::class);

        $this->assertSame($twilio, (new TwilioClient($twilio, $defaultFrom))->twilio());
    }

    public function testACallCanBeCreated(): void
    {
        $to          = '+15558675309';
        $defaultFrom = '+19418675309';
        $params      = [
            'url' => 'https://www.babdev.com',
        ];

        /** @var MockObject&CallList $calls */
        $calls = $this->createMock(CallList::class);
        $calls->expects($this->once())
            ->method('create')
            ->with($to, $defaultFrom, $params)
            ->willReturn($this->createMock(CallInstance::class));

        /** @var MockObject&Client $twilio */
        $twilio        = $this->createMock(Client::class);
        $twilio->calls = $calls;

        $this->assertInstanceOf(CallInstance::class, (new TwilioClient($twilio, $defaultFrom))->call($to, $params));
    }

    public function testACallCanBeCreatedWithACustomFromNumber(): void
    {
        $to          = '+15558675309';
        $defaultFrom = '+19418675309';
        $customFrom  = '+16518675309';
        $params      = [
            'url' => 'https://www.babdev.com',
        ];

        /** @var MockObject&CallList $calls */
        $calls = $this->createMock(CallList::class);
        $calls->expects($this->once())
            ->method('create')
            ->with($to, $customFrom, $params)
            ->willReturn($this->createMock(CallInstance::class));

        /** @var MockObject&Client $twilio */
        $twilio        = $this->createMock(Client::class);
        $twilio->calls = $calls;

        $this->assertInstanceOf(CallInstance::class, (new TwilioClient($twilio, $defaultFrom))->call($to, array_merge($params, ['from' => $customFrom])));
    }

    public function testAMessageCanBeSent(): void
    {
        $to = '+15558675309';
        $defaultFrom = '+19418675309';
        $message = 'Test Message';

        /** @var MockObject&MessageList $messages */
        $messages = $this->createMock(MessageList::class);
        $messages->expects($this->once())
            ->method('create')
            ->with(
                $to,
                [
                    'body' => $message,
                    'from' => $defaultFrom,
                ]
            )
            ->willReturn($this->createMock(MessageInstance::class));

        /** @var MockObject&Client $twilio */
        $twilio           = $this->createMock(Client::class);
        $twilio->messages = $messages;

        $this->assertInstanceOf(MessageInstance::class, (new TwilioClient($twilio, $defaultFrom))->message($to, $message));
    }

    public function testAMessageCanBeSentWithACustomFromNumber(): void
    {
        $to = '+15558675309';
        $defaultFrom = '+19418675309';
        $customFrom  = '+16518675309';
        $message = 'Test Message';

        /** @var MockObject&MessageList $messages */
        $messages = $this->createMock(MessageList::class);
        $messages->expects($this->once())
            ->method('create')
            ->with(
                $to,
                [
                    'body' => $message,
                    'from' => $customFrom,
                ]
            )
            ->willReturn($this->createMock(MessageInstance::class));

        /** @var MockObject&Client $twilio */
        $twilio           = $this->createMock(Client::class);
        $twilio->messages = $messages;

        $this->assertInstanceOf(MessageInstance::class, (new TwilioClient($twilio, $defaultFrom))->message($to, $message, ['from' => $customFrom]));
    }
}
