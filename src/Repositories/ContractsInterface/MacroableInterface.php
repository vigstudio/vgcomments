<?php

namespace Vigstudio\VgComment\Repositories\ContractsInterface;

interface MacroableInterface
{
    public function getAllMacros(): array;

    public function addMacro(string $model, string $name, \Closure $closure): void;

    public function removeMacro(string $model, string $name): bool;

    public function modelHasMacro(string $model, string $name): bool;

    public function modelsThatImplement(string $name): array;

    public function macrosForModel(string $model): array;
}
