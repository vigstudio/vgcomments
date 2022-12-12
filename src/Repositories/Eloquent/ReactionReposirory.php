<?php

namespace Vigstudio\VgComment\Repositories\Eloquent;

use Vigstudio\VgComment\Repositories\Interface\ReactionInterface;

class ReactionReposirory extends EloquentReposirory implements ReactionInterface
{
    public function getReactions(array $commentIds): array
    {
        $reactions = $this->query()
            ->whereIn('comment_id', $commentIds)
            ->get();

        return $reactions->toArray();
    }
}
