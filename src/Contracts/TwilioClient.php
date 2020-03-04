<?php

namespace BabDev\Twilio\Contracts;

use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Api\V2010\Account\CallInstance;
use Twilio\Rest\Api\V2010\Account\MessageInstance;
use Twilio\Rest\Client;

interface TwilioClient
{
    /**
     * Provides access to the REST API client from the Twilio SDK.
     *
     * @return Client
     */
    public function twilio(): Client;

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
    public function call(string $to, array $params = []): CallInstance;

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
    public function message(string $to, string $message, array $params = []): MessageInstance;
}
