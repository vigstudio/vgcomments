<?php

namespace Vigstudio\VgComment\Events;

use Vigstudio\VgComment\Models\Comment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class BroadcastCommentCreatedEvent implements ShouldBroadcast
{
    use InteractsWithSockets;
    use SerializesModels;

    public Comment $comment;

    public function __construct(Comment $comment)
    {
        $this->comment = $comment;

        $this->dontBroadcastToCurrentUser();
    }

    public function broadcastOn(): array
    {
        return ['vgcomment_' . vgcomment_page_hash($this->comment->page_id, $this->comment->commentable_id, $this->comment->commentable_type)];
    }

    public function broadcastAs()
    {
        return 'BroadcastCommentCreatedEvent';
    }
}
