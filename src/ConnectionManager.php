<?php

namespace BabDev\Twilio;

use BabDev\Twilio\Contracts\TwilioClient;
use Illuminate\Support\Manager;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Api\V2010\Account\CallInstance;
use Twilio\Rest\Api\V2010\Account\MessageInstance;
use Twilio\Rest\Client;

class ConnectionManager extends Manager implements TwilioClient
{
    /**
     * Get a connection instance.
     *
     * @param string|null $name
     *
     * @return TwilioClient
     */
    public function connection(?string $name = null)
    {
        return $this->driver($name);
    }

    /**
     * Create a new driver instance.
     *
     * @param string $driver
     *
     * @return TwilioClient
     *
     * @throws \InvalidArgumentException
     */
    protected function createDriver($driver)
    {
        try {
            return parent::createDriver($driver);
        } catch (\InvalidArgumentException $e) {
            $configKey = "twilio.connections.$driver";

            if ($this->config->has($configKey)) {
                // TODO - Build client
            }

            throw $e;
        }
    }

    /**
     * Provides access to the REST API client from the Twilio SDK.
     *
     * @return Client
     */
    public function twilio(): Client
    {
        return $this->connection()->twilio();
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
        return $this->connection()->call($to, $params);
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
        return $this->connection()->message($to, $message, $params);
    }
}
