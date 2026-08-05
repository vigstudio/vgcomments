<?php

namespace Vigstudio\VgComment\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Vigstudio\VgComment\Models\Comment;
use Vigstudio\VgComment\Models\Reaction;
use Vigstudio\VgComment\Models\Report;
use Vigstudio\VgComment\Models\Vote;
use Vigstudio\VgComment\Services\GetAuthenticatableService;
use Vigstudio\VgComment\Facades\MacroableFacades;
use Vigstudio\VgComment\Policies\CommentPolicy;
use Illuminate\Support\Facades\File;
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

            $authModel::resolveRelationUsing('votes', function ($model) {
                return $model->morphMany(Vote::class, 'voterable');
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

            MacroableFacades::addMacro($authModel, 'unReact', function (Comment $comment, string $type) {
                $reaction = $this->reactions()->where('comment_uuid', $comment->getUuid())->first();

                if (empty($reaction)) {
                    return false;
                }

                if ($reaction->type == $type) {
                    return $reaction->delete();
                }

                return false;
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

        // Publishing the assets.
        $this->publishes([__DIR__ . '/../../public' => public_path('vendor/vgcomments')], 'vgcomment-assets');

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

        // Read moderation_users at check-time (not boot-time) so config/env
        // updates apply without relying on a stale closure capture.
        Gate::define('vgcomment-moderate', function ($user) {
            $moderationUsers = Config::get('vgcomment.moderation_users') ?? [];

            foreach ($moderationUsers as $guard => $ids) {
                if (! is_array($ids) || $ids === []) {
                    continue;
                }

                $provider = config("auth.guards.{$guard}.provider");
                $model = $provider ? config("auth.providers.{$provider}.model") : null;

                if ($model && ! is_a($user, $model)) {
                    continue;
                }

                $userId = $user->getAuthIdentifier();
                $normalized = array_map(static fn ($id) => is_numeric($id) ? (int) $id : $id, $ids);

                if (in_array($userId, $ids, false) || in_array((int) $userId, $normalized, true)) {
                    return true;
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
            $settings = app(SettingInterface::class)->getAll();

            foreach ($settings as $key => $value) {
                Config::set('vgcomment.' . $key, $value);
            }
        }
    }

    public function provides(): array
    {
        return ['vgcomment.formatter'];
    }
}
