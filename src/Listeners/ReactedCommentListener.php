<?php

namespace Vigstudio\VgComment\Listeners;

use Vigstudio\VgComment\Events\ReactedCommentEvent;

class ReactedCommentListener
{
    public function handle(ReactedCommentEvent $event): void
    {
        $reaction = $event->reaction;
        $comment = $reaction->comment;
        $comment->reactions_data = $comment->reactions->groupBy('type')->map(function ($reactions) {
            return $reactions->count();
        })->toArray();
        $comment->point = $comment->reactions()->count() + $comment->replies()->count();
        $comment->save();
    }
}
