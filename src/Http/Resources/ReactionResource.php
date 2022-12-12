<?php

namespace Vigstudio\VgComment\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReactionResource extends JsonResource
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
            'comment_id' => $this->comment_id,
            'comment_uuid' => $this->comment_uuid,
            'type' => $this->type,
            'reactable_type' => $this->reactable_type,
            'reactable_id' => $this->reactable_id,
        ];
    }
}
