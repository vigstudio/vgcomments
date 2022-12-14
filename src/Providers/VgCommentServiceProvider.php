<?php

namespace Vigstudio\VgComment\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Vigstudio\VgComment\Models\Comment;
use Vigstudio\VgComment\Models\Reaction;
use Vigstudio\VgComment\Services\GetAuthenticatableService;
use Vigstudio\VgComment\Facades\MacroableFacades;
use Vigstudio\VgComment\Policies\CommentPolicy;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;

class VgCommentServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', 'vgcomment');

        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations', 'vgcomment');

        $this->mergeConfigFrom(__DIR__ . '/../../config/vgcomment.php', 'vgcomment');

        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'vgcomment');

        $this->loadRoutesFrom(__DIR__ . '/../../routes/routes.php');

        if ($this->app->runningInConsole()) {
            $this->definePublishing();
        }

        $this->bootMacros();
    }

    public function register()
    {
        File::requireOnce(__DIR__ . '/../../helpers/vgcomments.php');

        $this->registerServices($this->app);
        $this->registerGates();
    }

    protected function bootMacros()
    {
        $getProviders = GetAuthenticatableService::getProviders();

        foreach ($getProviders as $authModel) {
            if (! class_exists($authModel)) {
                continue;
            }

            $authModel::resolveRelationUsing('comments', function ($model) {
                return $model->morphMany(Comment::class, 'responder');
            });

            $authModel::resolveRelationUsing('reactions', function ($model) {
                return $model->morphMany(Reaction::class, 'reactable');
            });

            MacroableFacades::addMacro($authModel, 'react', function (Comment $comment, string $type) {
                $reaction = $this->reactions()->where('comment_uuid', $comment->getUuid())->first();

                if (! $reaction) {
                    return $this->reactions()->create([
                        'comment_id' => $comment->getKey(),
                        'comment_uuid' => $comment->getUuid(),
                        'type' => $type,
                    ]);
                }

                if ($reaction->type == $type) {
                    return $reaction;
                }

                $reaction->delete();

                return $this->reactions()->create([
                    'comment_id' => $comment->getKey(),
                    'comment_uuid' => $comment->getUuid(),
                    'type' => $type,
                ]);
            });
        }
    }

    protected function definePublishing()
    {
        // Publishing the config.
        $this->publishes([__DIR__ . '/../../config/vgcomment.php' => config_path('vgcomment.php')], 'vgcomment-config');

        // Publishing the translation files.
        $this->publishes([__DIR__ . '/../../resources/lang' => $this->app->langPath('vendor/vgcomment')], 'vgcomment-lang');
    }

    protected function registerServices($app)
    {
        $app->register(EventServiceProvider::class);
        $app->register(RepositoriesServiceProvider::class);
        $app->register(ContractsServiceProvider::class);
    }

    protected function registerGates()
    {
        Gate::policy(Comment::class, CommentPolicy::class);

        $moderationUsers = Config::get('vgcomment.moderation_users');

        Gate::define('vgcomment-moderate', function ($user) use ($moderationUsers) {
            foreach ($moderationUsers as $moderationUser) {
                if (Auth::guard($moderationUser[0])->check()) {
                    return $user->id == $moderationUser[1];
                }
            }

            return false;
        });
    }

    public function provides(): array
    {
        return ['vgcomment.formatter'];
    }
}
