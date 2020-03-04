<?php

namespace BabDev\Twilio;

use BabDev\Twilio\Contracts\TwilioClient as TwilioClientContract;
use Illuminate\Support\Manager;
use Twilio\Exceptions\ConfigurationException;
use Twilio\Exceptions\TwilioException;
use Twilio\Http\Client as HttpClient;
use Twilio\Rest\Api\V2010\Account\CallInstance;
use Twilio\Rest\Api\V2010\Account\MessageInstance;
use Twilio\Rest\Client as RestClient;

class ConnectionManager extends Manager implements TwilioClientContract
{
    /**
     * Get a connection instance.
     *
     * @param string|null $name
     *
     * @return TwilioClientContract
     *
     * @throws \InvalidArgumentException if the driver cannot be created
     */
    public function connection(?string $name = null)
    {
        return $this->driver($name);
    }

    /**
     * Get the default channel driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->config->get('twilio.default', 'twilio');
    }

    /**
     * Create a new driver instance.
     *
     * @param string $driver
     *
     * @return TwilioClientContract
     *
     * @throws \InvalidArgumentException if the driver cannot be created
     */
    protected function createDriver($driver)
    {
        try {
            return parent::createDriver($driver);
        } catch (\InvalidArgumentException $e) {
            if ($this->config->has("twilio.connections.$driver")) {
                try {
                    return $this->container->make(
                        TwilioClient::class,
                        [
                            'twilio' => $this->container->make(
                                RestClient::class,
                                [
                                    'username' => $this->config->get("twilio.connections.$driver.sid"),
                                    'password' => $this->config->get("twilio.connections.$driver.token"),
                                    'httpClient' => $this->container->make(HttpClient::class),
                                ]
                            ),
                            'from' => $this->config->get("twilio.connections.$driver.from"),
                        ]
                    );
                } catch (ConfigurationException $e) {
                    throw new \InvalidArgumentException("Driver [$driver] is not correctly configured.", $e->getCode(), $e);
                }
            }

            throw $e;
        }
    }

    /**
     * Provides access to the REST API client from the Twilio SDK.
     *
     * @return RestClient
     */
    public function twilio(): RestClient
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
