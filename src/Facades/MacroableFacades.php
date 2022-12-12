<?php

namespace Vigstudio\VgComment\Facades;

use Illuminate\Support\Facades\Facade;

class MacroableFacades extends Facade
{
    /**
     * @see \Vigstudio\VgComment\Support\MacroableModels
     *
     * @method getAllMacros()
     * @method addMacro(String $model, String $name, \Closure $closure)
     * @method removeMacro($model, String $name)
     * @method modelHasMacro($model, $name)
     * @method modelsThatImplement($name)
     * @method macrosForModel($model)
     * */
    protected static function getFacadeAccessor()
    {
        return 'macroable-models';
    }
}
