<?php

namespace Vigstudio\VgComment\Http\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationIlluminate;
use Vigstudio\VgComment\Repositories\Interface\CommentInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

trait CommentValidator
{
    protected function storeCommentValidator(Request $request): ValidationIlluminate
    {
        $minLength = $this->config['min_length'];
        $maxLength = $this->config['max_length'];
        $table = $this->config['table']['comments'];

        $validator = Validator::make(
            $request->all(),
            [
                'content' => [
                    'required',
                    'string',
                    "min:$minLength",
                    "max:$maxLength",
                ],
                'root_id' => [
                    'nullable',
                    "exists:$table,id,parent_id,NULL",
                ],
                'parent_id' => [
                    'nullable',
                    "required_with:root_id|exists:$table,id",
                ],
            ],
            [],
            [
                'content' => trans('vgcomment::validation.attributes.content'),
            ]
        )->after(function ($validator) use ($request) {
            if ($this->hasDupicate($request->all())) {
                $validator->errors()->add('content', trans('vgcomment::validation.errors.content_duplicate'));
            }
        });

        return $validator;
    }

    protected function updateCommentValidator(Request $request): ValidationIlluminate
    {
        $minLength = $this->config['min_length'];
        $maxLength = $this->config['max_length'];
        $table = $this->config['table']['comments'];

        $validator = Validator::make(
            $request->all(),
            [
                'content' => [
                    'required',
                    'string',
                    "min:$minLength",
                    "max:$maxLength",
                ],
            ],
            [],
            [
                'content' => trans('vgcomment::validation.attributes.content'),
            ]
        )->after(function ($validator) use ($request) {
            if ($this->hasDupicate($request->all())) {
                $validator->errors()->add('content', trans('vgcomment::validation.errors.content_duplicate'));
            }
        });

        return $validator;
    }

    protected function uploadValidator(Request $request): ValidationIlluminate
    {
        $rule = $this->config['upload_rules']['rules'];
        $max = $this->config['upload_rules']['max'];

        $validator = Validator::make(
            $request->all(),
            [
                'files.*' => $rule,
                'files' => 'max:' . $max,
            ],
            [
                'files.max' => trans('vgcomment::validation.files.max'),
            ],
            [
                'files.*' => trans('vgcomment::validation.attributes.files'),
                'files' => trans('vgcomment::validation.attributes.files'),
            ]
        )->after(function ($validator) use ($request) {
            if ($this->config['nsfw']) {
                foreach ($request['files'] as $file) {
                    $isNswf = Str::before($file->getMimeType(), '/') == 'image' ? $this->checkNswf($file) : false;
                    if ($isNswf) {
                        $validator->errors()->add('content', trans('vgcomment::validation.errors.nsfw'));
                    }
                }
            }
        });

        return $validator;
    }

    protected function hasDupicate(array $request): bool
    {
        $hasDupicate = app(CommentInterface::class)->hasDupicate($request);

        return $this->config['duplicates_check'] ? $hasDupicate : false;
    }

    protected function checkNswf(mixed $file)
    {
        $params = [
            'models' => 'nudity-2.0',
            'api_user' => $this->config['nsfw_api']['user'],
            'api_secret' => $this->config['nsfw_api']['key'],
        ];

        $response = Http::attach(
            'media',
            $file->get(),
            $file->getClientOriginalName()
        )
        ->post('https://api.sightengine.com/1.0/check.json', $params);

        $data = $response->json();

        if ($data['status'] != 'success') {
            throw new \Exception('Sightengine API error: ' . $data['error']['message']);
        }

        return $data['nudity']['sexual_display'] > 0.5;
    }
}
