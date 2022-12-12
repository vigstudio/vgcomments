<?php

namespace Vigstudio\VgComment\Http\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Vigstudio\VgComment\Services\GetAuthenticatableService;
use Illuminate\Contracts\Auth\Authenticatable;


trait ThrottlesPosts
{
    protected function tooManyAttempts(Request $request): bool
    {
        $throttle = config('vgcomment.throttle');

        return RateLimiter::tooManyAttempts(
            $this->key($request),
            $throttle['max_rate'],
            $throttle['per_minutes']
        );
    }

    protected function availableIn(Request $request): int
    {
        $seconds = RateLimiter::availableIn(
            $this->key($request)
        );

        return $seconds;
    }

    protected function getAuth(): Authenticatable|bool
    {
        return GetAuthenticatableService::get();
    }

    protected function key(Request $request)
    {
        $auth = $this->getAuth();
        if ($this->getAuth()) {
            return $auth->getAuthIdentifier() . '|' . $request->ip();
        }

        return $request->ip();
    }

    protected function incrementAttempts(Request $request)
    {
        RateLimiter::hit($this->key($request));
    }

    protected function clearAttempts(Request $request)
    {
        RateLimiter::clear($this->key($request));
    }
}
