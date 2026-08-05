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
        $author = $this->getAuthorAttribute();
        unset($author['email']);

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'content' => $this->content_html,
            'root_id' => $this->root_id,
            'parent_id' => $this->parent_id,
            'page_id' => $this->page_id,
            'created_at' => $this->created_at,
            'time' => $this->time,
            'status' => $this->status,
            'author' => $author,
            'avatar' => $this->getAuthorAvatarAttribute(),
            'policy' => $this->policy,
            'replies' => ReplieResource::collection($this->replies)->toArray($request),
            'files' => FileResource::collection($this->files)->toArray($request),
            'reactions' => ReactionResource::collection($this->reactions)->toArray($request),
            'votes' => [
                'upvotes' => (int) ($this->upvotes ?? 0),
                'downvotes' => (int) ($this->downvotes ?? 0),
                'score' => (int) ($this->upvotes ?? 0) - (int) ($this->downvotes ?? 0),
                'user_vote' => $this->user_vote,
            ],
        ];
    }
}
