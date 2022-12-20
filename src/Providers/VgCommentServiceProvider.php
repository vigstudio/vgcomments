<?php

namespace Vigstudio\VgComment\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Vigstudio\VgComment\Models\Comment;
use Vigstudio\VgComment\Models\Reaction;
use Vigstudio\VgComment\Models\Report;
use Vigstudio\VgComment\Services\GetAuthenticatableService;
use Vigstudio\VgComment\Facades\MacroableFacades;
use Vigstudio\VgComment\Policies\CommentPolicy;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Vigstudio\VgComment\Repositories\Interface\SettingInterface;

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
        $this->bootConfig();
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

            $authModel::resolveRelationUsing('reports', function ($model) {
                return $model->morphMany(Report::class, 'reporter');
            });

            MacroableFacades::addMacro($authModel, 'report', function (Comment $comment) {
                $report = $this->reports()->where('comment_uuid', $comment->getUuid())->first();

                if (! $report) {
                    return $this->reports()->create([
                        'comment_id' => $comment->getKey(),
                        'comment_uuid' => $comment->getUuid(),
                    ]);
                }

                return $report;
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

        if ($moderationUsers == null) {
            $moderationUsers = [];
        }

        Gate::define('vgcomment-moderate', function ($user) use ($moderationUsers) {
            foreach ($moderationUsers as $key => $moderationUser) {
                if (Auth::guard($key)->check()) {
                    return in_array($user->id, $moderationUser);
                }
            }

            return false;
        });

        $router = $this->app['router'];
        $router->aliasMiddleware('vgcomment-moderate', \Vigstudio\VgComment\Http\Middleware\ModerationUser::class);
    }

    protected function bootConfig()
    {
        if (Schema::hasTable(Config::get('vgcomment.table.settings'))) {
            $settings = app(SettingInterface::class)->all();
            foreach ($settings as $setting) {
                Config::set('vgcomment.' . $setting->key, $setting->value);
            }
        }
    }

    public function provides(): array
    {
        return ['vgcomment.formatter'];
    }
}
