<?php

namespace Vigstudio\VgComment\Repositories\ContractsInterface;

interface MacroableInterface
{
    public function getAllMacros();

    public function addMacro(string $model, string $name, \Closure $closure);

    public function removeMacro($model, string $name);

    public function modelHasMacro($model, $name);

    public function modelsThatImplement($name);

    public function macrosForModel($model);
}
