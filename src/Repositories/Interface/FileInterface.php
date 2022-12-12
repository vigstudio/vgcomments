<?php

namespace Vigstudio\VgComment\Repositories\Interface;

use Vigstudio\VgComment\Models\FileComment;
use Illuminate\Database\Eloquent\Collection;
use Vigstudio\VgComment\Models\Comment;

interface FileInterface extends EloquentInterface
{
    public function upload(array $files): Collection|bool;

    public function findByName(string $name): ?FileComment;

    public function findHash(string $hash): ?FileComment;

    public function registerFilesForComment(Comment $comment, array $file): bool;
}
