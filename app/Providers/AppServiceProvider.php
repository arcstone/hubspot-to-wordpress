<?php

namespace H2W\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use H2W\API\HubSpot;
use H2W\API\WordPress;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->configure('hubspot');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('hubspot.api', function () {
            return new Client([
                'base_uri'        => config('hubspot.base_uri'),
                'connect_timeout' => 30.0,
                'query'           => [
                    'hapikey' => config('hubspot.apiKey'),
                ],
            ]);
        });

        $this->app->when(HubSpot::class)
            ->needs(ClientInterface::class)
            ->give(function ($app) {
                return $app['hubspot.api'];
            });

        $this->app->singleton('wordpress.api', function () {
            return new Client([
                'base_uri'        => config('wordpress.base_uri'),
                'connect_timeout' => 30.0,
                'headers'         => [
                    'Authorization' => 'Basic ' . base64_encode(config('wordpress.username') . ':' .
                                                                config('wordpress.password')),
                ],
            ]);
        });

        $this->app->when(WordPress::class)
            ->needs(ClientInterface::class)
            ->give(function ($app) {
                return $app['wordpress.api'];
            });

        $this->app->singleton(WordPress::class, function ($app) {
            return $app->build(WordPress::class);
        });

        $this->app->singleton(HubSpot::class, function ($app) {
            return $app->build(HubSpot::class);
        });
    }
}
