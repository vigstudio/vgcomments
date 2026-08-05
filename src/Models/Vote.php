<?php

namespace Vigstudio\VgComment\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Vigstudio\VgComment\Services\GetAuthenticatableService;

class Vote extends BaseModel
{
    public const TABLE = 'votes';

    public const UP = 1;

    public const DOWN = -1;

    protected $fillable = [
        'comment_id',
        'comment_uuid',
        'value',
        'voterable_type',
        'voterable_id',
    ];

    protected $casts = [
        'value' => 'integer',
    ];

    public function voterable(): MorphTo
    {
        return $this->morphTo();
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'comment_id');
    }

    public function getUserVotedAttribute(): bool
    {
        $auth = GetAuthenticatableService::get();

        if ($auth !== false) {
            return $this->voterable_type === get_class($auth)
                && (string) $this->voterable_id === (string) $auth->getAuthIdentifier();
        }

        $token = session()->get('vgcomment.guest_reactor');

        if (! is_string($token) || $token === '') {
            return false;
        }

        $guestId = (int) sprintf('%u', crc32($token));

        return $this->voterable_type === 'vgcomment_guest'
            && (int) $this->voterable_id === $guestId;
    }
}
