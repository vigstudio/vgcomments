<?php

namespace Vigstudio\VgComment\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Vigstudio\VgComment\Repositories\ContractsInterface\ModeratorInterface;
use Vigstudio\VgComment\Repositories\Interface\EloquentInterface;
use Illuminate\Http\Request;

abstract class EloquentReposirory implements EloquentInterface
{
    protected Model $model;

    protected array $config;

    protected ModeratorInterface $moderator;

    public function __construct(Model $model, array $config, ModeratorInterface $moderator)
    {
        $this->model = $model;
        $this->config = $config;
        $this->moderator = $moderator;
    }

    protected function query(): Builder
    {
        return $this->model->newQuery();
    }

    public function findByUuid(string $uuid): mixed
    {
        return $this->query()->where('uuid', $uuid)->first();
    }

    public function find(int $id): mixed
    {
        return $this->query()->find($id);
    }

    public function create(array $attributes): mixed
    {
        return $this->query()->create($attributes);
    }

    public function update(array $attributes): mixed
    {
        return $this->query()->update($attributes);
    }

    public function delete(int $id): bool
    {
        return $this->find($id)->delete();
    }

    protected function makeRequest(array $request): Request
    {
        return new Request($request);
    }
}
