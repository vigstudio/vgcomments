<?php

namespace Vigstudio\VgComment\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReplieResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'content' => $this->content_html,
            'user_agent' => $this->user_agent,
            'permalink' => $this->permalink,
            'root_id' => $this->root_id,
            'page_id' => $this->page_id,
            'parent_id' => $this->parent_id,
            'created_at' => $this->created_at,
            'time' => $this->time,
            'status' => $this->status,
            'commentable' => $this->commentable,
            'author' => $this->getAuthorAttribute(),
            'avatar' => $this->getAuthorAvatarAttribute(),
            'policy' => $this->policy,
            'parent' => $this->parent->toArray(),
            'files' => FileResource::collection($this->files)->toArray($request),
            'reactions' => ReactionResource::collection($this->reactions)->toArray($request),
        ];
    }
}
