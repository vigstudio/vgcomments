<?php

namespace Vigstudio\VgComment\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Contracts\Auth\Authenticatable|bool getAuth()
 * @method static \Illuminate\Http\Resources\Json\JsonResource|\Illuminate\Pagination\LengthAwarePaginator get(array $req = [], bool $jsonResource = true)
 * @method static \Illuminate\Http\Resources\Json\JsonResource getAdmin(array $req = [], bool $jsonResource = true)
 * @method static \Illuminate\Http\Resources\Json\JsonResource findById(int $id)
 * @method static \Illuminate\Http\Resources\Json\JsonResource store(array $req)
 * @method static bool update(array $req, string $uuid)
 * @method static bool delete(string $uuid)
 * @method static \Illuminate\Http\Resources\Json\JsonResource|bool upload($files)
 * @method static bool registerFilesForComment(\Vigstudio\VgComment\Models\Comment $comment, array $files)
 * @method static bool reaction(string $uuid, string $type)
 * @method static bool deleteReaction(string $uuid, string $type)
 * @method static bool report(string $uuid)
 * @method static \Illuminate\Auth\Access\Response authorize(mixed $ability, mixed|array $arguments = [])
 * @method static \Illuminate\Auth\Access\Response authorizeForUser(\Illuminate\Contracts\Auth\Authenticatable|mixed $user, mixed $ability, mixed|array $arguments = [])
 * @method static void authorizeResource(string|array $model, string|array|null $parameter = null, array $options = [], \Illuminate\Http\Request|null $request = null)
 *
 * @see \Vigstudio\VgComment\Services\CommentService
 */

class CommentServiceFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'vgcomment.services';
    }
}
