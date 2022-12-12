<?php

namespace Vigstudio\VgComment\Providers;

use Illuminate\Contracts\Cache\Repository as CacheInterface;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Vigstudio\VgComment\Repositories\ContractsInterface\CommentFormatterInterface;
use Vigstudio\VgComment\Repositories\ContractsInterface\MacroableInterface;
use Vigstudio\VgComment\Repositories\ContractsInterface\ModeratorInterface;
use Vigstudio\VgComment\Support\CommentFormatter;
use Vigstudio\VgComment\Support\MacroableModels;
use Vigstudio\VgComment\Support\Moderator;
use Vigstudio\VgComment\Services\CommentService;

class ContractsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(CommentFormatterInterface::class, function () {
            return new CommentFormatter(Config::get('vgcomment'), app(CacheInterface::class));
        });

        $this->app->bind(ModeratorInterface::class, function () {
            return new Moderator(Config::get('vgcomment'), app(CommentFormatterInterface::class));
        });

        $this->app->bind(MacroableInterface::class, function () {
            return new MacroableModels();
        });

        $this->app->singleton('vgcomment.formatter', function () {
            return app(CommentFormatterInterface::class);
        });

        $this->app->singleton('vgcomment.moderator', function () {
            return app(ModeratorInterface::class);
        });

        $this->app->singleton('macroable-models', function () {
            return app(MacroableModels::class);
        });

        $this->app->singleton('vgcomment.services', function () {
            return app(CommentService::class);
        });
    }
}
