<?php

namespace Vigstudio\VgComment\Models;

use Illuminate\Database\Eloquent\Relations\hasOne;
use Illuminate\Support\Facades\Storage;

class FileComment extends BaseModel
{
    public const TABLE = 'files';

    protected $fillable = [
        'hash',
        'comment_id',
        'comment_uuid',
        'name',
        'path',
        'file_name',
        'mime',
        'mime_type',
        'disk',
        'size',
    ];

    protected $appends = [
        'url_stream',
    ];

    public function comment(): hasOne
    {
        return $this->hasOne(Comment::class, 'comment_id');
    }

    public function getUrlStreamAttribute(): string
    {
        return route('vgcomments.files.stream', [$this->getUuid(), pathinfo($this->path, PATHINFO_EXTENSION)]);
    }

    public function toResponse(): mixed
    {
        return $this->buildResponse('attachment');
    }

    public function toInlineResponse(): mixed
    {
        return $this->buildResponse('inline');
    }

    private function buildResponse(string $contentDispositionType): mixed
    {
        $downloadHeaders = [
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-Type' => $this->mime_type,
            'Content-Length' => $this->size,
            'Content-Disposition' => $contentDispositionType . 'inline; filename="' . $this->file_name . '"',
            'Pragma' => 'public',
        ];

        return response()->stream(function () {
            $stream = $this->stream();

            fpassthru($stream);

            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, $downloadHeaders);
    }

    public function stream(): mixed
    {
        return $this->getDisk()->readStream($this->path);
    }

    protected function getDiskName(): string
    {
        return $this->disk;
    }

    protected function getDisk(): \Illuminate\Filesystem\FilesystemAdapter
    {
        return Storage::disk($this->getDiskName());
    }
}
