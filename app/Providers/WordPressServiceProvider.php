<?php

namespace H2W\Providers;

use H2W\API\WPAPI;
use H2W\API\WPAPIContract;
use Illuminate\Support\ServiceProvider;

class WordPressServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->configure('wordpress');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(WPAPIContract::class, function () {
            return new WPAPI(
                config('wordpress.base_uri'),
                config('wordpress.username'),
                config('wordpress.password')
            );
        });
    }
}
