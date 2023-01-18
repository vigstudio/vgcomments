<?php

namespace Vigstudio\VgComment\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
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
            'parent_id' => $this->parent_id,
            'content' => $this->content_html,
            // 'files' => FileResource::collection($this->files),
            'reactions' => ReactionResource::collection($this->reactions)->toArray($request),
            'author_name' => $this->author_name,
            'author_email' => $this->author_email,
            'author_url' => $this->author_url,
            'author_avatar' => $this->avatar_url,
            'user_agent' => $this->user_agent,
            'permalink' => $this->permalink,
            'root_id' => $this->root_id,
            'parent_id' => $this->parent_id,
            'created_at' => $this->created_at,
            'status' => $this->status,
        ];
    }
}
