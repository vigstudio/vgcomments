<?php

namespace Vigstudio\VgComment\Http\Traits;

use Illuminate\Http\Request;
use Illuminate\Validation\Validator as ValidationIlluminate;
use Vigstudio\VgComment\Services\ValidatorService;

trait CommentValidator
{
    protected function storeCommentValidator(Request $request): ValidationIlluminate
    {
        return app(ValidatorService::class)->storeCommentValidator($request);
    }

    protected function updateCommentValidator(Request $request): ValidationIlluminate
    {
        return app(ValidatorService::class)->updateCommentValidator($request);
    }

    protected function uploadValidator(Request $request): ValidationIlluminate
    {
        return app(ValidatorService::class)->uploadValidator($request);
    }

}
