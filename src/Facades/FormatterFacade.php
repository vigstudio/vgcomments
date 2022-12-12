<?php

namespace Vigstudio\VgComment\Facades;

use Illuminate\Support\Facades\Facade;

class FormatterFacade extends Facade
{
    /**
     * @see \Vigstudio\VgComment\Repositories\ContractsInterfaces\CommentFormatterInterface
     *
     * @method parse(string $text): string
     * @method unparse(string $xml): string
     * @method render(string $xml): string
     * @method flush(): void
     * */
    protected static function getFacadeAccessor(): string
    {
        return 'vgcomment.formatter';
    }
}
