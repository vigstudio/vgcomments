<?php

namespace Vigstudio\VgComment\Repositories\Eloquent;

use Vigstudio\VgComment\Repositories\Interface\SettingInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Collection;
use Vigstudio\VgComment\Facades\FormatterFacade;
use Illuminate\Contracts\Cache\Repository as CacheInterface;

class SettingReposirory extends EloquentReposirory implements SettingInterface
{
    public function set(Request $request): Collection
    {
        $only_key = collect($this->config)->keys()->toArray();

        $inputs = $request->only(Arr::except($only_key, ['table', 'connection', 'moderation_users']));

        foreach ($inputs as $key => $value) {
            $parseValue = $this->parseBoolean($value);

            $this->updateOrCreate(['key' => $key], ['value' => $parseValue]);
            $this->forgetCache($key);
            $this->rememberCache($key, $parseValue);
        }

        FormatterFacade::flush();

        return $this->query()->get();
    }

    public function get(string $key): mixed
    {
        $value = $this->getCache($key);

        if ($value) {
            return $value;
        }

        $value = $this->query()->where('key', $key)->first();

        if ($value) {
            $this->rememberCache($key, $value->value);

            return $value->value;
        }

        return $this->config[$key];
    }

    public function forget(string $key): bool
    {
        $this->forgetCache($key);

        return $this->query()->where('key', $key)->delete();
    }

    protected function parseBoolean(string|array|bool $value): mixed
    {
        if ($value === 'true' || $value === 'false') {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        return $value;
    }

    protected function getCache(string $key): string|array
    {
        return app(CacheInterface::class)->get($this->key($key));
    }

    protected function rememberCache(string $key, string|array|bool $value): mixed
    {
        return app(CacheInterface::class)->rememberForever($this->key($key), function () use ($value) {
            return $value;
        });
    }

    protected function forgetCache(string $key): bool
    {
        return app(CacheInterface::class)->forget($key);
    }

    protected function key(string $key): string
    {
        return 'vgcomments.setting.' . $key;
    }
}
