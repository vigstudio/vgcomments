<?php

namespace Vigstudio\VgComment\Policies;

use Illuminate\Support\Facades\Config;
use Vigstudio\VgComment\Models\Comment;
use Illuminate\Contracts\Auth\Authenticatable;

class CommentPolicy
{
    protected $config;

    public function __construct()
    {
        $this->config = Config::get('vgcomment');
    }

    public function moderate(?Authenticatable $auth): bool
    {
        return $auth->can('vgcomment-moderate');
    }

    public function update(?Authenticatable $auth, Comment $comment): bool
    {
        if ($auth->can('moderate', Comment::class)) {
            return true;
        }

        if (! $comment->approved()) {
            return false;
        }

        if ($auth->comments()->where('id', $comment->id)->exists()) {
            return true;
        }

        return false;
    }

    public function delete(?Authenticatable $auth, Comment $comment): bool
    {
        if ($auth->can('moderate', Comment::class)) {
            return true;
        }

        if (! $comment->approved() && ! $comment->pending()) {
            return false;
        }

        if ($auth->comments()->where('id', $comment->id)->exists()) {
            return true;
        }

        return false;
    }

    public function report(?Authenticatable $auth, Comment $comment): bool
    {
        if (! $comment->approved()) {
            return false;
        }

        return true;
    }
}
