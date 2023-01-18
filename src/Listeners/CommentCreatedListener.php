<?php

namespace Vigstudio\VgComment\Listeners;

use Illuminate\Support\Facades\Config;
use Vigstudio\VgComment\Events\CommentCreatedEvent;
use Vigstudio\VgComment\Events\BroadcastCommentCreatedEvent;

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

        if (Config::get('vgcomment.broadcast') && $comment->approved()) {
            event(new BroadcastCommentCreatedEvent($comment));
        }
    }
}
