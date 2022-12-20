<?php

namespace Vigstudio\VgComment\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ModerationUser
{
    public function handle(Request $request, Closure $next)
    {
        foreach (array_keys(config('auth.guards')) as $guard) {
            if (Auth::guard($guard)->check()) {
                if (Auth::guard($guard)->user()->can('vgcomment-moderate')) {
                    return $next($request);
                }
            }
        }

        return abort(403);
    }
}
