<?php

namespace Vigstudio\VgComment\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array getAllMacros()
 * @method static void addMacro(string $model, string $name, \Closure $closure)
 * @method static bool removeMacro(string $model, string $name)
 * @method static bool modelHasMacro(string $model, string $name)
 * @method static array modelsThatImplement(string $name)
 * @method static array macrosForModel(string $model)
 *
 * @see \Vigstudio\VgComment\Repositories\ContractsInterface\MacroableInterface
 */

class MacroableFacades extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'macroable-models';
    }
}
