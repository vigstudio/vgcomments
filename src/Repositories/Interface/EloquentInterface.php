<?php

namespace Vigstudio\VgComment\Repositories\Interface;

interface EloquentInterface
{
    public function findByUuid(string $uuid): mixed;

    public function find(int $id): mixed;

    public function create(array $attributes): mixed;

    public function update(array $attributes): mixed;

    public function delete(int $id): bool;
}
