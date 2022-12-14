<?php

namespace Vigstudio\VgComment\Models;

use Illuminate\Database\Eloquent\Relations\hasMany;
use Illuminate\Database\Eloquent\Relations\hasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Vigstudio\VgComment\Facades\FormatterFacade;
use Vigstudio\VgComment\Events\CommentCreatedEvent;

class Comment extends BaseModel
{
    use Traits\HasAttachment;
    use Traits\HasAuthorComment;

    public const TABLE = 'comments';

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
        'reactions_data',
    ];

    protected $casts = [
        'reactions_data' => 'array',
    ];

    protected $dispatchesEvents = [
        'created' => CommentCreatedEvent::class,
    ];

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function replies(): hasMany
    {
        return $this->hasMany(static::class, 'root_id');
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

    public function reports()
    {
        return $this->hasMany(Report::class, 'comment_id');
    }

    public function files()
    {
        return $this->hasMany(FileComment::class, 'comment_id');
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
        return FormatterFacade::render($this->attributes['content']);
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
