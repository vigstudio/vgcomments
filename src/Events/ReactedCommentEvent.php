<?php

namespace Vigstudio\VgComment\Events;

use Vigstudio\VgComment\Models\Reaction;

class ReactedCommentEvent
{
    public Reaction $reaction;

    public function __construct(Reaction $reaction)
    {
        $this->reaction = $reaction;
    }
}
