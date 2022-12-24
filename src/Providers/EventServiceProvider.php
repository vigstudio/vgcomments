<?php

namespace Vigstudio\VgComment\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Vigstudio\VgComment\Events\FormatterConfiguratorEvent;
use Vigstudio\VgComment\Listeners\FormatterConfiguratorListener;
use Vigstudio\VgComment\Events\ReactedCommentEvent;
use Vigstudio\VgComment\Listeners\ReactedCommentListener;
use Vigstudio\VgComment\Events\CommentCreatedEvent;
use Vigstudio\VgComment\Listeners\CommentCreatedListener;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        FormatterConfiguratorEvent::class => [
            FormatterConfiguratorListener::class,
        ],
        ReactedCommentEvent::class => [
            ReactedCommentListener::class,
        ],
        CommentCreatedEvent::class => [
            CommentCreatedListener::class,
        ],
    ];
}
