<?php

namespace Vigstudio\VgComment\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Vigstudio\VgComment\Events\FormatterConfiguratorEvent;
use Vigstudio\VgComment\Listeners\FormatterConfiguratorListener;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        FormatterConfiguratorEvent::class => [
            FormatterConfiguratorListener::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}
