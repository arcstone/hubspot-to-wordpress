<?php

namespace H2W\Providers;

use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'H2W\Events\SomeEvent' => [
            'H2W\Listeners\EventListener',
        ],
    ];
}
