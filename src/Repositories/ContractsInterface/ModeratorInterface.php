<?php

namespace Vigstudio\VgComment\Repositories\ContractsInterface;

use Vigstudio\VgComment\Models\Comment;

interface ModeratorInterface
{
    public function determineStatus(Comment $comment): string;
}
