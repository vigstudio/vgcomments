<?php

namespace Vigstudio\VgComment\Models;

use Illuminate\Database\Eloquent\Relations\hasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Vigstudio\VgComment\Services\GetAuthenticatableService;
use Vigstudio\VgComment\Events\ReactedCommentEvent;

class Reaction extends BaseModel
{
    public const TABLE = 'reactions';

    protected $fillable = [
        'comment_id',
        'comment_uuid',
        'type',
        'reactable_type',
        'reactable_id',
    ];

    protected $dispatchesEvents = [
        'created' => ReactedCommentEvent::class,
    ];

    public function reactable(): MorphTo
    {
        return $this->morphTo();
    }

    public function comment(): hasOne
    {
        return $this->hasOne(Comment::class, 'id', 'comment_id');
    }

    public function getUserReactedAttribute(): bool
    {
        $auth = GetAuthenticatableService::get();

        if ($auth === false) {
            return false;
        }

        return $this->reactable_type === get_class($auth) && $this->reactable_id === $auth->getAuthIdentifier();
    }
}
