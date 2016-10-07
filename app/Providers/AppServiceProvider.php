<?php

namespace H2W\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use H2W\API\HubSpot;
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
        $this->app->when(HubSpot::class)
            ->needs(ClientInterface::class)
            ->give(function () {
                return new Client([
                    'base_uri' => config('hubspot.base_uri'),
                    'timeout'  => 2.0,
                    'query'    => [
                        'hapikey' => config('hubspot.apiKey'),
                    ],
                ]);
            });
    }
}
