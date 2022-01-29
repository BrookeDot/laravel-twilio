<?php

namespace BabDev\Twilio\Providers;

use BabDev\Twilio\ConnectionManager;
use BabDev\Twilio\Contracts\TwilioClient;
use BabDev\Twilio\Notifications\Channels\TwilioChannel;
use BabDev\Twilio\Twilio\Http\LaravelHttpClient;
use GuzzleHttp\Client as Guzzle;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Http\Client\Factory;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;
use Twilio\Http\Client as TwilioHttpClient;
use Twilio\Http\CurlClient;
use Twilio\Http\GuzzleClient;

final class TwilioProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Get the services provided by the provider.
     *
     * @return array<string|class-string>
     */
    public function provides(): array
    {
        return [
            ConnectionManager::class,
            TwilioClient::class,

            TwilioHttpClient::class,
        ];
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->publishes(
            [
                __DIR__ . '/../../config/twilio.php' => config_path('twilio.php'),
            ],
            'config'
        );
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->registerConnectionManager();
        $this->registerHttpClient();
        $this->registerNotificationChannel();

        $this->mergeConfigFrom(__DIR__ . '/../../config/twilio.php', 'twilio');
    }

    /**
     * Registers the binding for the connection manager.
     */
    private function registerConnectionManager(): void
    {
        $this->app->singleton(
            ConnectionManager::class,
            static function (Application $app): ConnectionManager {
                return new ConnectionManager($app);
            }
        );

        $this->app->alias(ConnectionManager::class, TwilioClient::class);
    }

    /**
     * Registers the binding for the HTTP client.
     */
    private function registerHttpClient(): void
    {
        $this->app->bind(
            TwilioHttpClient::class,
            static function (Application $app): TwilioHttpClient {
                // If Guzzle is installed, then we will either use Laravel's native client or Guzzle directly
                if (class_exists(Guzzle::class)) {
                    // Use Laravel's HTTP client if able
                    if (class_exists(Factory::class)) {
                        return new LaravelHttpClient($app->make(Factory::class));
                    }

                    return new GuzzleClient(new Guzzle());
                }

                // Default to the curl client
                return new CurlClient();
            }
        );
    }

    /**
     * Registers the binding for the notification channel.
     */
    private function registerNotificationChannel(): void
    {
        Notification::resolved(static function (ChannelManager $manager): void {
            $manager->extend(
                'twilio',
                static function (Application $app): TwilioChannel {
                    /** @var Repository $config */
                    $config = $app->make('config');

                    /** @var ConnectionManager $manager */
                    $manager = $app->make(ConnectionManager::class);

                    return new TwilioChannel(
                        $manager->connection(
                            $config->get('twilio.notification_channel', $config->get('twilio.default', 'twilio'))
                        )
                    );
                }
            );
        });
    }
}
