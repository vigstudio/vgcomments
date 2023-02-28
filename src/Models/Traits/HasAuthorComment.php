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

        return \Avatar::create($this->getAuthorNameAttribute())->toGravatar(['d' => $this->config['gravatar_imageset']]);
    }

    public function getAuthorAttribute()
    {
        if ($this->responder) {
            return [
                'name' => $this->responder->name,
                'url' => $this->responder->url,
                'email' => $this->responder->email,
            ];
        }

        return [
            'name' => $this->attributes['author_name'],
            'url' => $this->attributes['author_url'],
            'email' => $this->attributes['author_email'],
        ];
    }

    public function getAuthorNameAttribute()
    {
        // if ($this->responder) {
        //     return $this->responder->name;
        // }

        return $this->attributes['author_name'];
    }

    public function getAuthorUrlAttribute()
    {
        // if ($this->responder) {
        //     return $this->responder->url;
        // }

        return $this->attributes['author_url'];
    }

    public function getAuthorEmailAttribute()
    {
        // if ($this->responder) {
        //     return $this->responder->email;
        // }

        return $this->attributes['author_email'];
    }
}
