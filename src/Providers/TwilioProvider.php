<?php

namespace BabDev\Twilio\Providers;

use Illuminate\Support\ServiceProvider;

final class TwilioProvider extends ServiceProvider
{
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
        $this->mergeConfigFrom(__DIR__ . '/../../config/twilio.php', 'twilio');
    }
}
