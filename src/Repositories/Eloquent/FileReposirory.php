<?php

namespace Vigstudio\VgComment\Repositories\Eloquent;

use Illuminate\Support\Str;
use Vigstudio\VgComment\Models\FileComment;
use Vigstudio\VgComment\Repositories\Interface\FileInterface;
use Vigstudio\VgComment\Services\UploadedFileComment;
use Illuminate\Database\Eloquent\Collection;
use Vigstudio\VgComment\Http\Traits\CommentValidator;
use Vigstudio\VgComment\Http\Traits\ThrottlesPosts;
use Vigstudio\VgComment\Models\Comment;

class FileReposirory extends EloquentReposirory implements FileInterface
{
    use CommentValidator;
    use ThrottlesPosts;

    public function upload(array $files): Collection|bool
    {
        $request = $this->makeRequest([
            'files' => $files,
        ]);

        $validator = $this->uploadValidator($request);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                session()->push('alert', ['error', $error]);
            }

            return false;
        }

        if ($this->tooManyAttempts($request)) {
            $seconds = $this->availableIn($request);

            session()->push('alert', ['error', trans('vgcomment::comment.throttle_max_rate', compact('seconds'))]);

            return false;
        }

        try {
            $collection = new Collection();

            foreach ($request->input('files') as $file) {
                $store = $this->store($file);
                $collection->push($store);
            }

            $this->incrementAttempts($request);

            session()->push('alert', ['success', trans('vgcomment::comment.upload_success')]);

            return $collection;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function findByName(string $name): ?FileComment
    {
        return $this->query()->where('name', $name)->first();
    }

    public function findHash(string $hash): ?FileComment
    {
        return $this->query()->where('hash', $hash)->first();
    }

    public function registerFilesForComment(Comment $comment, array $files): bool
    {
        preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $comment->content, $match);

        try {
            foreach ($match[0] as $img) {
                $uuid = Str::between($img, 'files/', '.');
                $imgData = $this->findByUuid($uuid);

                if ($imgData) {
                    $imgData->update([
                        'comment_uuid' => $comment->uuid,
                        'comment_id' => $comment->id,
                    ]);
                }
            }

            foreach ($files as $file) {
                $fileData = $this->findByUuid($file['uuid']);

                if ($fileData) {
                    $fileData->update([
                        'comment_uuid' => $comment->uuid,
                        'comment_id' => $comment->id,
                    ]);
                }
            }

            return true;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    protected function store(mixed $file): FileComment
    {
        $hash = $this->hashFile($file->path());

        $has = $this->findHash($hash);

        if ($has) {
            $newFile = $has->replicate([
                'comment_uuid',
                'comment_id',
                'uuid',
            ]);

            $newFile->save();

            return $newFile;
        }

        $mine = Str::before($file->getMimeType(), '/');
        $name = $file->hashName();
        $path = $file->store('/' . $this->config['prefix'] . '/' . $mine);

        $fileComment = $this->create([
            'hash' => $hash,
            'name' => $name,
            'path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime' => $mine,
            'mime_type' => $file->getMimeType(),
            'disk' => $this->config['disk_filesystem'],
            'size' => $file->getSize(),
        ]);

        $file->delete();

        return $fileComment;
    }

    protected function hashFile(string $path): string
    {
        return hash_file('sha256', $path);
    }

    protected function convertFilesystemOject(mixed $file): UploadedFileComment
    {
        return UploadedFileComment::create($file->path());
    }
}
