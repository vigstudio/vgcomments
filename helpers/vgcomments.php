<?php

use Illuminate\Support\Facades\Config;
use Vigstudio\VgComment\Models\Comment;
use Vigstudio\VgComment\Services\GetAuthenticatableService;
use Illuminate\Contracts\Auth\Authenticatable;

if (! function_exists('vgcomment_config')) {
    function vgcomment_config(): array
    {
        return Config::get('vgcomment');
    }
}

if (! function_exists('vgcomment_auth')) {
    function vgcomment_auth(): Authenticatable|bool
    {
        return GetAuthenticatableService::get();
    }
}

if (! function_exists('vgcomment_policy')) {
    function vgcomment_policy(int $commentId, string $policy): bool
    {
        $comment = Comment::find($commentId);
        $auth = GetAuthenticatableService::get();

        return $auth ? $auth->can($policy, $comment) : false;
    }
}

if (! function_exists('vgcomment_page_hash')) {
    function vgcomment_page_hash(string $page_id = null, int $commentable_id = null, string $commentable_type = null): string
    {
        if ($page_id) {
            return md5($page_id);
        }

        return md5($commentable_id . '|' . $commentable_type);
    }
}
