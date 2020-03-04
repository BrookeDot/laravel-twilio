<?php

namespace BabDev\Twilio\Providers;

use BabDev\Twilio\ConnectionManager;
use BabDev\Twilio\Contracts\TwilioClient;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

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
        $this->app->singleton(
            'babdev.twilio.manager',
            static function (Application $app): ConnectionManager {
                return new ConnectionManager($app);
            }
        );

        $this->app->alias('babdev.twilio.manager', ConnectionManager::class);
        $this->app->alias('babdev.twilio.manager', TwilioClient::class);

        $this->mergeConfigFrom(__DIR__ . '/../../config/twilio.php', 'twilio');
    }
}
