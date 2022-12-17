<?php

namespace Vigstudio\VgComment\Repositories\Interface;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;

interface SettingInterface extends EloquentInterface
{
    public function set(Request $request): Collection;

    public function get(string $key): mixed;

    public function forget(string $key): bool;
}
