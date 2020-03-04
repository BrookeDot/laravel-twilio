<?php

namespace BabDev\Twilio\Providers;

use BabDev\Twilio\ConnectionManager;
use BabDev\Twilio\Contracts\TwilioClient;
use BabDev\Twilio\Twilio\Http\LaravelHttpClient;
use GuzzleHttp\Client as Guzzle;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\ServiceProvider;
use Twilio\Http\Client as TwilioHttpClient;
use Twilio\Http\CurlClient;
use Twilio\Http\GuzzleClient;

final class TwilioProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'babdev.twilio.manager',
            ConnectionManager::class,
            TwilioClient::class,

            'babdev.twilio.http_client',
            TwilioHttpClient::class,
        ];
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
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
     *
     * @return void
     */
    public function register(): void
    {
        $this->registerConnectionManager();
        $this->registerHttpClient();

        $this->mergeConfigFrom(__DIR__ . '/../../config/twilio.php', 'twilio');
    }

    /**
     * Registers the binding for the connection manager.
     *
     * @return void
     */
    private function registerConnectionManager(): void
    {
        $this->app->singleton(
            'babdev.twilio.manager',
            static function (Application $app): ConnectionManager {
                return new ConnectionManager($app);
            }
        );

        $this->app->alias('babdev.twilio.manager', ConnectionManager::class);
        $this->app->alias('babdev.twilio.manager', TwilioClient::class);
    }

    /**
     * Registers the binding for the HTTP client.
     *
     * @return void
     */
    private function registerHttpClient(): void
    {
        $this->app->bind(
            'babdev.twilio.http_client',
            static function (Application $app): TwilioHttpClient {
                // If Guzzle is installed, then we will either use Laravel's native client or Guzzle directly
                if (\class_exists(Guzzle::class)) {
                    // Use Laravel's HTTP client if able
                    if (\class_exists(Factory::class)) {
                        return new LaravelHttpClient($app->make(Factory::class));
                    }

                    return new GuzzleClient(new Guzzle());
                }

                // Default to the curl client
                return new CurlClient();
            }
        );

        $this->app->alias('babdev.twilio.http_client', TwilioHttpClient::class);
    }
}
