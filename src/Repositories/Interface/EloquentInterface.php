<?php

namespace Vigstudio\VgComment\Repositories\Interface;

interface EloquentInterface
{
    public function all(): mixed;

    public function findByUuid(string $uuid): mixed;

    public function find(int $id): mixed;

    public function create(array $attributes): mixed;

    public function update(array $attributes): mixed;

    public function delete(int $id): bool;

    public function updateOrCreate(array $attributes, array $values = []): mixed;
}
