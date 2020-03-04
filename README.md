# Twilio SDK Integration for Laravel [![Build Status](https://travis-ci.com/BabDev/laravel-twilio.svg?branch=master)](https://travis-ci.com/BabDev/laravel-twilio)

Laravel package integrating the Twilio SDK into your Laravel application.

## Installation

To install this package, run the following [Composer](https://getcomposer.org/) command:

```sh
composer require babdev/laravel-twilio
```

If your application is not using package discovery, you will need to add the service provider to your `config/app.php` file:

```sh
BabDev\Twilio\Providers\TwilioProvider::class,
```

Likewise, you will also need to register the facade in your `config/app.php` file if not using package discovery:

```sh
'TwilioClient' => BabDev\Twilio\Facades\TwilioClient::class,
``` 

## Usage

Using the `ConnectionManager`, you can quickly create calls and send SMS messages through Twilio's REST API using their PHP SDK. By default, the `ConnectionManager` fulfills the `TwilioClient` contract and can be used by injecting the service into your class/method, retrieving the service from the container, or using the `TwilioClient` facade.

```php
namespace App\Http\Controllers;

use BabDev\Twilio\ConnectionManager;
use BabDev\Twilio\Contracts\TwilioClient;
use Illuminate\Http\Request;

final class MessagingController
{
    private $twilio;

    public function __construct(ConnectionManager $twilio)
    {
        $this->twilio = $twilio;
    }

    public function myConstructorAction()
    {
        // Fetch a connection from the manager and send a message
        $connection = $this->twilio->connection();
        $connection->message('+15555555555', 'My test message');

        // Or, send a message through the manager using the default connection
        $this->twilio->message('+15555555555', 'My test message');
    }

    public function myInjectedAction(Request $request, TwilioClient $twilio)
    {
        // This is the default connection from your manager
        $twilio->message('+15555555555', 'My test message');
    }

    public function myAppAction()
    {
        /** @var ConnectionManager $twilio */
        $twilio = app(ConnectionManager::class);
        $twilio->message('+15555555555', 'My test message');
    }

    public function myFacadeAction()
    {
        // The facade uses the connection manager, so you may retrieve any connection through it
        \TwilioClient::connection()->message('+15555555555', 'My test message');
    }
}
```

## Multiple Connections

If your application uses multiple sets of REST API credentials for the Twilio API, you can add the data for multiple connections to the package's configuration. First, you should publish this package's configuration:

```sh
php artisan vendor:publish --provider="BabDev\Twilio\Providers\TwilioProvider" --tag="config"
```

Then, in your newly created `config/twilio.php` file, you can add new connections to the `connections` array. Note, the default "twilio" connection has been created for you and uses environment variables by default. You are free to change the default connection for your application and the name of the "twilio" connection if desired.

```php
<?php

return [
    'default' => env('TWILIO_CONNECTION', 'twilio'),

    'connections' => [
        'twilio' => [
            'sid' => env('TWILIO_API_SID', ''),
            'token' => env('TWILIO_API_AUTH_TOKEN', ''),
            'from' => env('TWILIO_API_FROM_NUMBER', ''),
        ],

        'my_new_connection' => [
            'sid' => 'SID-1',
            'token' => 'TOKEN-1',
            'from' => 'PHONE-1',
        ],
    ],
];
```

## Customizing Client Creation

You can customize the setup of `TwilioClient` instances using the `ConnectionManager::extend()` method, this allows you to define a custom callback to be used for creating a client. You may either override the creation of a named connection from your configuration, or dynamically create a new connection.

```php
<?php

namespace App\Providers;

use App\Twilio\TwilioClient;
use BabDev\Twilio\Contracts\TwilioClient as TwilioClientContract;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use Twilio\Http\Client as HttpClient;
use Twilio\Rest\Client as RestClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        \TwilioClient::extend(
            'custom',
            function (Container $container): TwilioClientContract {
                /*
                 * Create a custom client from your application.
                 *
                 * For your convenience, you can use the Laravel container to create the
                 * Twilio\Rest\Client SDK class and its internal Twilio\Http\Client dependency
                 */
                return $container->make(
                    TwilioClient::class,
                    [
                        'twilio' => $container->make(
                            RestClient::class,
                            [
                                'username' => 'my_username',
                                'password' => 'my_password',
                                'httpClient' => $container->make(HttpClient::class),
                            ]
                        ),
                        'from' => 'my_sender_number',
                    ]
                );
            }
        );
    }
}
```

## HTTP Client

This package attempts to create an appropriate `Twilio\Http\Client` for the SDK based on the packages available in your application, using the following preferences:

- If using Laravel 7 and Guzzle is available, a client using [Laravel's HTTP client](https://laravel.com/docs/http-client) is used
- If Guzzle is available, the Twilio SDK's Guzzle client is used
- If neither of the above criteria are met, the Twilio SDK's Curl client is used

If you need to customize the HTTP client used by default in your application, you can extend the "`Twilio\Http\Client`" service this package creates to use your own HTTP client.
