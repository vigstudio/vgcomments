<?php

namespace Vigstudio\VgComment\Http\Traits;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Vigstudio\VgComment\Services\GetAuthenticatableService;

trait ThrottlesPosts
{
    protected function tooManyAttempts(Request $request): bool
    {
        $maxRate = (int) config('vgcomment.throttle_max_rate', 10);

        return RateLimiter::tooManyAttempts($this->key($request), $maxRate);
    }

    protected function availableIn(Request $request): int
    {
        return RateLimiter::availableIn($this->key($request));
    }

    protected function getAuth(): Authenticatable|bool
    {
        return GetAuthenticatableService::get();
    }

    protected function key(Request $request): string
    {
        $auth = $this->getAuth();

        if ($auth) {
            return 'vgcomment|'.$auth->getAuthIdentifier().'|'.$request->ip();
        }

        return 'vgcomment|guest|'.$request->ip();
    }

    protected function incrementAttempts(Request $request): void
    {
        $decaySeconds = max(1, (int) config('vgcomment.throttle_per_minutes', 1)) * 60;

        RateLimiter::hit($this->key($request), $decaySeconds);
    }

    protected function clearAttempts(Request $request): void
    {
        RateLimiter::clear($this->key($request));
    }
}
