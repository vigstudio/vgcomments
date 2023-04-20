<?php

namespace Vigstudio\VgComment\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string parse(string $text)
 * @method static string unparse(string $xml)
 * @method static string render(string $xml)
 * @method static void flush()
 *
 * @see \Vigstudio\VgComment\Repositories\ContractsInterface\CommentFormatterInterface
 */

class FormatterFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'vgcomment.formatter';
    }
}
