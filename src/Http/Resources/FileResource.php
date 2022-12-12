<?php

namespace Vigstudio\VgComment\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileResource extends JsonResource
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
            'uuid' => $this->uuid,
            'name' => $this->name,
            'file_name' => $this->file_name,
            'url_stream' => $this->url_stream,
            'size' => $this->size,
            'mime_type' => $this->mime_type,
            'mime' => $this->mime,
        ];
    }
}
