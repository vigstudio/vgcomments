<?php

namespace Vigstudio\VgComment\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReplieResource extends JsonResource
{
    public function toArray($request): array
    {
        $author = $this->getAuthorAttribute();
        unset($author['email']);

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'content' => $this->content_html,
            'root_id' => $this->root_id,
            'page_id' => $this->page_id,
            'parent_id' => $this->parent_id,
            'created_at' => $this->created_at,
            'time' => $this->time,
            'status' => $this->status,
            'author' => $author,
            'avatar' => $this->getAuthorAvatarAttribute(),
            'policy' => $this->policy,
            'parent' => $this->parent ? [
                'id' => $this->parent->id,
                'uuid' => $this->parent->uuid,
                'author_name' => $this->parent->author_name,
            ] : null,
            'files' => FileResource::collection($this->files)->toArray($request),
            'reactions' => ReactionResource::collection($this->reactions)->toArray($request),
        ];
    }
}
