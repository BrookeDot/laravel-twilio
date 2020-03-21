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

## Configuration

This package can be configured using environment variables in your `.env` file for its basic usage. For advanced use cases, you can publish this package's configuration with this command:
                                                                                                                            
```sh
php artisan vendor:publish --provider="BabDev\Twilio\Providers\TwilioProvider" --tag="config"
```

The below environment variables should be set:

- `TWILIO_CONNECTION`: The name of the default Twilio API connection for your application; if using a single connection this does not need to be changed
- `TWILIO_NOTIFICATION_CHANNEL_CONNECTION`: If using Laravel's notifications system, the name of a Twilio API connection to use in the notification channel (defaulting to your default connection); if using a single connection this does not need to be changed
- `TWILIO_API_SID`: The Twilio API SID to use for the default Twilio API connection
- `TWILIO_API_AUTH_TOKEN`: The Twilio API authentication token to use for the default Twilio API connection
- `TWILIO_API_FROM_NUMBER`: The default sending phone number to use for the default Twilio API connection, note the sending phone number can be changed on a per-message basis

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

## Notifications

Messages can be sent as part of Laravel's [notifications system](https://laravel.com/docs/notifications). A notifiable (such as a User model) should include the "twilio" channel in its `via()` method. When routing the notification, the phone number the message should be sent to should be returned.

```php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        // This application's users can receive notifications by mail and Twilio SMS
        return ['mail', 'twilio'];
    }

    /**
     * Get the notification routing information for the Twilio driver.
     * 
     * @param Notification $notification
     * 
     * @return string
     */
    public function routeNotificationForTwilio($notification)
    {
        return $this->mobile_number;
    }
}
```

For notifications that support being sent as an SMS, you should define a `toTwilio` method on the notification class. This method will receive a $notifiable entity and should return a string containing the message text.

```php
namespace App\Notifications;

use Illuminate\Notifications\Notification;

final class PasswordExpiredNotification extends Notification
{
    /**
     * Get the Twilio / SMS representation of the notification.
     *
     * @param mixed $notifiable
     * 
     * @return string
     */
    public function toTwilio($notifiable)
    {
        // The $notifiable in this example is your User model
        return sprintf('Hello %s, this is a note that the password for your %s account has expired.', $notifiable->name, config('app.name'));
    }
}
```

Your default connection is used for the notification channel by default. If your application utilizes multiple Twilio API connections, you can set the connection which should be used using the `TWILIO_NOTIFICATION_CHANNEL_CONNECTION` environment variable.

## Multiple Connections

If your application uses multiple sets of REST API credentials for the Twilio API, you can add the data for multiple connections to the package's configuration. If you have not already, you will need to publish this package's configuration.

Then, in your newly created `config/twilio.php` file, you can add new connections to the `connections` array. Note, the default "twilio" connection has been created for you and uses environment variables by default. You are free to change the default connection for your application and the name of the "twilio" connection if desired.

```php
<?php

return [
    'default' => env('TWILIO_CONNECTION', 'twilio'),

    'notification_channel' => env('TWILIO_NOTIFICATION_CHANNEL_CONNECTION', env('TWILIO_CONNECTION', 'twilio')),

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
