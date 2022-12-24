<?php

namespace Vigstudio\VgComment\Models\Traits;

use Illuminate\Database\Eloquent\Relations\MorphTo;

trait HasAuthorComment
{
    public function responder(): MorphTo
    {
        return $this->morphTo();
    }

    public function getAuthorAvatarAttribute()
    {
        if ($this->config['gravatar']) {
            return \Avatar::create($this->getAuthorEmailAttribute())->toGravatar(['d' => $this->config['gravatar_imageset']]);
        }

        return \Avatar::create($this->getAuthorNameAttribute())->toGravatar(['d' => 'monsterid']);
    }

    public function getAuthorNameAttribute()
    {
        if ($this->responder) {
            return $this->responder->name;
        }

        return $this->author_name;
    }

    public function getAuthorUrlAttribute()
    {
        if ($this->responder) {
            return $this->responder->url;
        }

        return $this->author_url;
    }

    public function getAuthorEmailAttribute()
    {
        if ($this->responder) {
            return $this->responder->email;
        }

        return $this->author_email;
    }
}
