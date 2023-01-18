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

if (! function_exists('vgcomment_page_hash')) {
    function vgcomment_page_hash(string $page_id = null, int $commentable_id = null, string $commentable_type = null): string
    {
        if ($page_id) {
            return md5($page_id);
        }

        return md5($commentable_id . '|' . $commentable_type);
    }
}
