<?php

namespace Vigstudio\VgComment\Listeners;

use Vigstudio\VgComment\Events\CommentCreatedEvent;

class CommentCreatedListener
{
    public function handle(CommentCreatedEvent $event): void
    {
        $comment = $event->comment;

        $parent = $comment->parent;
        if (! empty($parent->reactions)) {
            $parent->reactions_data = $parent->reactions->groupBy('type')->map(function ($reactions) {
                return $reactions->count();
            })->toArray();

            $parent->point = $parent->reactions()->count() + $parent->replies()->count();
            $parent->save();
        }
    }
}
