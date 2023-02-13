<?php

namespace Vigstudio\VgComment\Policies;

use Illuminate\Support\Facades\Config;
use Vigstudio\VgComment\Models\Comment;
use Illuminate\Contracts\Auth\Authenticatable;
use Vigstudio\VgComment\Services\GetAuthenticatableService;

class CommentPolicy
{
    protected $config;
    protected $auth;

    public function __construct()
    {
        $this->config = Config::get('vgcomment');
        $this->auth = GetAuthenticatableService::get();
    }

    public function moderate(?Authenticatable $auth): bool
    {
        $auth = $this->auth;

        return $auth->can('vgcomment-moderate');
    }

    public function update(?Authenticatable $auth, Comment $comment): bool
    {
        $auth = $this->auth;
        if (! $auth) {
            return false;
        }

        if ($auth->can('moderate', Comment::class)) {
            return true;
        }

        if (! $comment->approved()) {
            return false;
        }

        if ($comment->responder_type == $auth->getMorphClass() && $comment->responder_id == $auth->getKey()) {
            return true;
        }

        return false;
    }

    public function delete(?Authenticatable $auth, Comment $comment): bool
    {
        $auth = $this->auth;

        if (! $auth) {
            return false;
        }

        if ($auth->can('moderate', Comment::class)) {
            return true;
        }

        if (! $comment->approved() && ! $comment->pending()) {
            return false;
        }

        if ($comment->responder_type == $auth->getMorphClass() && $comment->responder_id == $auth->getKey()) {
            return true;
        }

        return false;
    }

    public function report(?Authenticatable $auth, Comment $comment): bool
    {
        $auth = $this->auth;

        if (! $comment->approved()) {
            return false;
        }

        return true;
    }
}
