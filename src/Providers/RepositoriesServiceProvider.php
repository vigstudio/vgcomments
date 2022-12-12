<?php

namespace Vigstudio\VgComment\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Vigstudio\VgComment\Models\Comment;
use Vigstudio\VgComment\Models\FileComment;
use Vigstudio\VgComment\Models\Reaction;
use Vigstudio\VgComment\Repositories\ContractsInterface\ModeratorInterface;
use Vigstudio\VgComment\Repositories\Eloquent\CommentReposirory;
use Vigstudio\VgComment\Repositories\Eloquent\FileReposirory;
use Vigstudio\VgComment\Repositories\Eloquent\ReactionReposirory;
use Vigstudio\VgComment\Repositories\Interface\CommentInterface;
use Vigstudio\VgComment\Repositories\Interface\FileInterface;
use Vigstudio\VgComment\Repositories\Interface\ReactionInterface;

class RepositoriesServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(CommentInterface::class, function () {
            return new CommentReposirory(new Comment(), Config::get('vgcomment'), app(ModeratorInterface::class));
        });
        $this->app->bind(FileInterface::class, function () {
            return new FileReposirory(new FileComment(), Config::get('vgcomment'), app(ModeratorInterface::class));
        });
        $this->app->bind(ReactionInterface::class, function () {
            return new ReactionReposirory(new Reaction(), Config::get('vgcomment'), app(ModeratorInterface::class));
        });
    }
}
