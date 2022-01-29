<?php

namespace BabDev\Twilio\Facades;

use BabDev\Twilio\ConnectionManager;
use BabDev\Twilio\Contracts\TwilioClient as TwilioClientContract;
use Illuminate\Support\Facades\Facade;
use Twilio\Rest\Api\V2010\Account\CallInstance;
use Twilio\Rest\Api\V2010\Account\MessageInstance;
use Twilio\Rest\Client;

/**
 * @method static ConnectionManager extend($name, \Closure $callback)
 * @method static TwilioClientContract connection(string $name = null)
 * @method static Client twilio()
 * @method static CallInstance call(string $to, array $params = [])
 * @method static MessageInstance message(string $to, string $message, array $params = [])
 *
 * @see ConnectionManager
 */
final class TwilioClient extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return ConnectionManager::class;
    }
}
