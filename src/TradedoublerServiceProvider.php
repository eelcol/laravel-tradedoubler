<?php

namespace Eelcol\LaravelTradedoubler;

use Eelcol\LaravelTradedoubler\Support\Connectors\Tradedoubler;
use Illuminate\Support\ServiceProvider;

class TradedoublerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/tradedoubler.php'   => config_path('tradedoubler.php'),
            __DIR__.'/../database/settings/'      => base_path('database/settings'),
        ], 'laravel-tradedoubler');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/tradedoubler.php', 'tradedoubler');

        $this->app->bind('tradedoubler', function ($app) {
            return new Tradedoubler(config('tradedoubler'));
        });
    }
}
