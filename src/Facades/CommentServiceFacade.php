<?php

namespace Vigstudio\VgComment\Facades;

use Illuminate\Support\Facades\Facade;

class CommentServiceFacade extends Facade
{
    /**
     * @see Vigstudio\VgComment\Services\CommentService
     *
     * */
    protected static function getFacadeAccessor(): string
    {
        return 'vgcomment.services';
    }
}
