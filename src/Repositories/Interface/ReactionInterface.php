<?php

namespace Vigstudio\VgComment\Repositories\Interface;

interface ReactionInterface extends EloquentInterface
{
    public function getReactions(array $commentIds): array;
}
