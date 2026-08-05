<?php

namespace Vigstudio\VgComment\Models;

use Illuminate\Database\Eloquent\Relations\hasMany;
use Illuminate\Database\Eloquent\Relations\hasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Vigstudio\VgComment\Facades\FormatterFacade;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Gate;
use Vigstudio\VgComment\Services\GetAuthenticatableService;

class Comment extends BaseModel
{
    use Traits\HasAttachment;
    use Traits\HasAuthorComment;
    use SoftDeletes;

    public const TABLE = 'comments';

    public const STATUSES = [
        self::STATUS_SPAM,
        self::STATUS_TRASH,
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
    ];

    public const STATUS_SPAM = 'spam';

    public const STATUS_TRASH = 'trash';

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    protected $fillable = [
        'page_id',
        'commentable_type',
        'commentable_id',
        'responder_type',
        'responder_id',
        'author_name',
        'author_email',
        'author_url',
        'author_ip',
        'user_agent',
        'permalink',
        'content',
        'status',
        'root_id',
        'parent_id',
        'point',
        'upvotes',
        'downvotes',
        'reactions_data',
    ];

    protected $casts = [
        'reactions_data' => 'array',
        'upvotes' => 'integer',
        'downvotes' => 'integer',
    ];

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * All replies in the thread (same root_id). Loaded flat for one query;
     * call nestReplies() to rebuild parent_id hierarchy for display.
     */
    public function replies(): hasMany
    {
        return $this->hasMany(static::class, 'root_id')->with([
            'reactions' => function ($query) {
                return $query->cacheTags(['vigcomment_reaction_releation_' . $this->uuid]);
            },
            'votes' => function ($query) {
                return $query->cacheTags(['vigcomment_vote_releation_' . $this->uuid]);
            },
            'parent' => function ($query) {
                return $query->cacheTags(['vigcomment_reaction_parent_' . $this->uuid]);
            },
            'files' => function ($query) {
                return $query->cacheTags(['vigcomment_reaction_files_' . $this->uuid]);
            },
            'responder' => function ($query) {
                return $query->cacheTags(['vigcomment_reaction_responder_' . $this->uuid]);
            },
        ]);
    }

    /**
     * Nest flat root_id replies into a parent_id tree for recursive .vg-thread rendering.
     * Safe to call multiple times; no-ops when already nested or only direct children exist.
     */
    public function nestReplies(): self
    {
        if (! $this->relationLoaded('replies')) {
            return $this;
        }

        $flat = $this->replies;

        if ($flat->isEmpty()) {
            return $this;
        }

        $rootId = (int) $this->id;
        $needsNesting = $flat->contains(fn ($reply) => (int) $reply->parent_id !== $rootId);

        if (! $needsNesting) {
            // Direct children only (or already nested) — ensure empty child reply relations exist.
            $flat->each(function (self $reply) {
                if (! $reply->relationLoaded('replies')) {
                    $reply->setRelation('replies', $reply->newCollection());
                }
            });

            return $this;
        }

        $this->setRelation('replies', static::buildReplyTree($flat, $rootId));

        return $this;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, self>  $flat
     * @return \Illuminate\Support\Collection<int, self>
     */
    public static function buildReplyTree($flat, int $parentId)
    {
        return $flat
            ->filter(fn (self $reply) => (int) $reply->parent_id === $parentId)
            ->values()
            ->map(function (self $reply) use ($flat) {
                $reply->setRelation('replies', static::buildReplyTree($flat, (int) $reply->id));

                return $reply;
            });
    }

    public function parent(): hasOne
    {
        return $this->hasOne(static::class, 'id', 'parent_id');
    }

    public function root(): hasOne
    {
        return $this->hasOne(static::class, 'id', 'root_id');
    }

    public function reactions()
    {
        return $this->hasMany(Reaction::class, 'comment_id');
    }

    public function votes()
    {
        return $this->hasMany(Vote::class, 'comment_id');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'comment_id');
    }

    public function getScoreAttribute(): int
    {
        return (int) ($this->upvotes ?? 0) - (int) ($this->downvotes ?? 0);
    }

    /**
     * Current viewer's vote: 1 (up), -1 (down), or null.
     */
    public function getUserVoteAttribute(): ?int
    {
        if ($this->relationLoaded('votes')) {
            $mine = $this->votes->first(fn (Vote $vote) => (bool) $vote->user_voted);

            return $mine ? (int) $mine->value : null;
        }

        $auth = GetAuthenticatableService::get();

        if ($auth !== false) {
            $vote = $this->votes()
                ->where('voterable_type', get_class($auth))
                ->where('voterable_id', $auth->getAuthIdentifier())
                ->first();

            return $vote ? (int) $vote->value : null;
        }

        $token = session()->get('vgcomment.guest_reactor');

        if (! is_string($token) || $token === '') {
            return null;
        }

        $guestId = (int) sprintf('%u', crc32($token));
        $vote = $this->votes()
            ->where('voterable_type', 'vgcomment_guest')
            ->where('voterable_id', $guestId)
            ->first();

        return $vote ? (int) $vote->value : null;
    }

    public function files()
    {
        return $this->hasMany(FileComment::class, 'comment_id');
    }

    public function getPolicyAttribute()
    {
        return [
            'update' => Gate::allows('update', $this),
            'delete' => Gate::allows('delete', $this),
            'report' => Gate::allows('report', $this),
        ];
    }

    public function reactionsGroup()
    {
        return $this->reactions->groupBy('type');
    }

    public function getTimeAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function setContentAttribute($value)
    {
        $this->attributes['content'] = FormatterFacade::parse($value);
    }

    public function getContentAttribute($value)
    {
        return FormatterFacade::unparse($value);
    }

    public function getContentHtmlAttribute()
    {
        $xml = (string) ($this->attributes['content'] ?? '');
        $html = FormatterFacade::render($xml);

        // Re-parse stored XML when emphasis markers were left literal
        // (e.g. older comments with `**bold **` trailing-space typos).
        if ($html !== '' && preg_match('/\*\*|~~/', $html)) {
            $html = FormatterFacade::render(FormatterFacade::parse(FormatterFacade::unparse($xml)));
        }

        return $html;
    }

    public function getUrlAttribute()
    {
        return $this->permalink . '#vgcomment-' . $this->uuid;
    }

    public function getStatusNameAttribute()
    {
        return trans('vgcomment::comment.status.' . $this->status);
    }

    public function approved()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function pending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function spam()
    {
        return $this->status === self::STATUS_SPAM;
    }

    public function trash()
    {
        return $this->status === self::STATUS_TRASH;
    }
}
