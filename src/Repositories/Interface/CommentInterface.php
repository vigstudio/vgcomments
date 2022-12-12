<?php

namespace Vigstudio\VgComment\Repositories\Interface;

use Vigstudio\VgComment\Models\Comment;
use Illuminate\Database\Eloquent\Builder;

interface CommentInterface extends EloquentInterface
{
    public function store(array $request): Comment|bool;

    public function getComments(array $request): Builder;

    public function hasDupicate(array $request): bool;
}
