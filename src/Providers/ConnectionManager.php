<?php

namespace BabDev\Twilio\Providers;

use Illuminate\Support\Manager;

class ConnectionManager extends Manager
{
    /**
     * Get a connection instance.
     *
     * @param string|null $name
     *
     * @return mixed
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
     * @return mixed
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
}
