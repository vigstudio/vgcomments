<?php

namespace Vigstudio\VgComment\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Vigstudio\VgComment\Models\FileComment;

trait HasAttachment
{
    public static function bootHasAttachment()
    {
        static::created(function (Model $model) {
            if (! empty($model->uuid)) {
                FileComment::where('comment_uuid', $model->getUuid())
                ->update([
                    'comment_id' => $model->getKey(),
                ]);
            }
        });
    }
}
