<?php

namespace Vigstudio\VgComment\Models;

use Illuminate\Database\Eloquent\Relations\hasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Vigstudio\VgComment\Services\GetAuthenticatableService;

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

    public function reactable(): MorphTo
    {
        return $this->morphTo();
    }

    public function comment(): hasOne
    {
        return $this->hasOne(Comment::class, 'comment_id');
    }

    public function getUserReactedAttribute(): bool
    {
        $auth = GetAuthenticatableService::get();

        return $this->reactable_type === get_class($auth) && $this->reactable_id === $auth->getAuthIdentifier();
    }
}
