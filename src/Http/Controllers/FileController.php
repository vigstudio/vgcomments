<?php

namespace Vigstudio\VgComment\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Vigstudio\VgComment\Repositories\Interface\FileInterface;

class FileController extends Controller
{
    protected FileInterface $fileRepository;

    public function __construct(FileInterface $fileRepository)
    {
        $this->fileRepository = $fileRepository;
    }

    public function stream(Request $request, string $uuid)
    {
        $image = $this->fileRepository->findByUuid($uuid);

        return $image ? $image->toInlineResponse() : abort(404);
    }
}
