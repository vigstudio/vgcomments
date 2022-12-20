<?php

namespace Vigstudio\VgComment\Models;

use Illuminate\Database\Eloquent\Relations\hasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Vigstudio\VgComment\Services\GetAuthenticatableService;

class Report extends BaseModel
{
    public const TABLE = 'reports';

    protected $fillable = [
        'comment_id',
        'comment_uuid',
        'reporter_type',
        'reporter_id',
    ];

    public function reporter(): MorphTo
    {
        return $this->morphTo();
    }

    public function comment(): hasOne
    {
        return $this->hasOne(Comment::class, 'comment_id');
    }

    public function getAuthReporterAttribute(): bool
    {
        $auth = GetAuthenticatableService::get();

        return $this->reporter_type === get_class($auth) && $this->reporter_id === $auth->getAuthIdentifier();
    }
}
