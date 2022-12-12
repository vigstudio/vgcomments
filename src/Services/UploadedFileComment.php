<?php

namespace Vigstudio\VgComment\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadedFileComment extends UploadedFile
{
    protected $storage;

    protected $path;

    public function __construct($path = '')
    {
        $config = config('vgcomment');

        $this->disk = $config['disk_filesystem'];
        $this->storage = Storage::disk($this->disk);
        $this->path = Str::replace($this->storage->path(''), '/', $path);

        $tmpFile = tmpfile();

        parent::__construct(stream_get_meta_data($tmpFile)['uri'], $this->path);
    }

    public static function create($filePath)
    {
        return new static($filePath);
    }

    public function getPath(): string
    {
        return $this->storage->path($this->path);
    }

    public function isValid(): bool
    {
        return true;
    }

    public function getSize(): int
    {
        return (int) $this->storage->size($this->path);
    }

    public function getMimeType(): string
    {
        $mimeType = $this->storage->mimeType($this->path);

        return $mimeType;
    }

    public function getFilename(): string
    {
        return $this->getName($this->path);
    }

    public function getRealPath(): string
    {
        return $this->storage->path($this->path);
    }

    public function getClientOriginalName(): string
    {
        return $this->extractOriginalNameFromFilePath($this->path);
    }

    public function extractOriginalNameFromFilePath($path)
    {
        return base64_decode(head(explode('-', last(explode('-meta', str($path)->replace('_', '/'))))));
    }

    public function exists()
    {
        return $this->storage->exists($this->path);
    }

    public function get()
    {
        return $this->storage->get($this->path);
    }

    public function delete()
    {
        return $this->storage->delete($this->path);
    }

    public function readStream()
    {
        return $this->storage->readStream($this->path);
    }

    public function storeAs($path, $name, $options = [])
    {
        $options = $this->parseOptions($options);
        $disk = Arr::pull($options, 'disk') ?: $this->disk;

        $newPath = trim($path . '/' . $name, '/');

        $this->storage->put($newPath, $this->storage->readStream($this->path), $options);

        return $newPath;
    }

    public function store($path, $options = [])
    {
        return $this->storeAs($path, $this->hashName(), $this->parseOptions($options));
    }
}
