<?php

namespace Vigstudio\VgComment\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;

class GetAuthenticatableService
{
    public static function get(): Authenticatable|bool
    {
        foreach (array_keys(config('auth.guards')) as $guard) {
            if (Auth::guard($guard)->check()) {
                return Auth::guard($guard)->user();
            }
        }

        return false;
    }

    public static function getProviders(): array
    {
        return Arr::pluck(config('auth.providers'), 'model');
    }
}
