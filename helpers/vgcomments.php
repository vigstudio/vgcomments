<?php

use Vigstudio\VgComment\Models\Comment;
use Vigstudio\VgComment\Services\GetAuthenticatableService;

if (! function_exists('vgcomment_policy')) {
    function vgcomment_policy(int $commentId, string $policy): bool
    {
        $comment = Comment::find($commentId);
        $auth = GetAuthenticatableService::get();

        return $auth ? $auth->can($policy, $comment) : false;
    }
}
