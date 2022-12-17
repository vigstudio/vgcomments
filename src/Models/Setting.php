<?php

namespace Vigstudio\VgComment\Models;

class Setting extends BaseModel
{
    public const TABLE = 'settings';

    public $timestamps = false;

    protected $fillable = [
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'array',
    ];
}
