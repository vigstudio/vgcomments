<?php

namespace Vigstudio\VgComment\Events;

use Vigstudio\VgComment\Models\Comment;

class CommentCreatedEvent
{
    public Comment $comment;

    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }
}
