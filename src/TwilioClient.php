<?php

namespace BabDev\Twilio;

use BabDev\Twilio\Contracts\TwilioClient as TwilioClientContract;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Api\V2010\Account\CallInstance;
use Twilio\Rest\Api\V2010\Account\MessageInstance;
use Twilio\Rest\Client;

final class TwilioClient implements TwilioClientContract
{
    /**
     * @var Client
     */
    private $twilio;

    /**
     * @var string
     */
    private $from;

    /**
     * Creates a new Twilio client instance.
     *
     * @param Client $twilio The Twilio SDK client.
     * @param string $from   The default from number to use.
     */
    public function __construct(Client $twilio, string $from)
    {
        $this->twilio = $twilio;
        $this->from = $from;
    }

    /**
     * Provides access to the REST API client from the Twilio SDK.
     *
     * @return Client
     */
    public function twilio(): Client
    {
        return $this->twilio;
    }

    /**
     * Create a call through the Twilio API.
     *
     * @param string $to     The phone number to create a call to.
     * @param array  $params Optional arguments for the created call.
     *
     * @return CallInstance
     *
     * @throws TwilioException on Twilio API failure
     */
    public function call(string $to, array $params = []): CallInstance
    {
        // Allows specifying a custom from number with fallback
        $from = $params['from'] ?? $this->from;
        unset($params['from']);

        return $this->twilio()->calls->create($to, $from, $params);
    }

    /**
     * Send a SMS through the Twilio API.
     *
     * @param string $to      The phone number to send the SMS to.
     * @param string $message The message body to send.
     * @param array  $params  Optional arguments for the SMS.
     *
     * @return MessageInstance
     *
     * @throws TwilioException on Twilio API failure
     */
    public function message(string $to, string $message, array $params = []): MessageInstance
    {
        $params['body'] = $message;

        // Allows specifying a custom from number with fallback
        $params['from'] = $params['from'] ?? $this->from;

        return $this->twilio()->messages->create($to, $params);
    }
}
