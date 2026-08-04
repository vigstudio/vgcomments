<?php

namespace Vigstudio\VgComment\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator as ValidationIlluminate;
use Vigstudio\VgComment\Repositories\Interface\CommentInterface;

class ValidatorService
{
    public array $config;

    public function __construct()
    {
        $this->config = vgcomment_config();
    }

    public function storeCommentValidator(Request $request): ValidationIlluminate
    {
        $minLength = $this->config['min_length'];
        $maxLength = $this->config['max_length'];
        $table = $this->config['table']['comments'];
        $allowGuest = (bool) $this->config['allow_guests'];
        $auth = vgcomment_auth();
        $allowedTypes = $this->config['allowed_commentable_types'] ?? [];

        $validator = Validator::make(
            $request->all(),
            [
                'content' => [
                    'required',
                    'string',
                    "min:$minLength",
                    "max:$maxLength",
                ],
                'page_id' => [
                    'nullable',
                    'string',
                    'max:255',
                    'required_without:commentable_type',
                ],
                'commentable_type' => [
                    'nullable',
                    'string',
                    'required_with:commentable_id',
                    Rule::requiredIf(fn () => blank($request->input('page_id'))),
                    function (string $attribute, mixed $value, \Closure $fail) use ($allowedTypes) {
                        if (blank($value)) {
                            return;
                        }

                        if (empty($allowedTypes)
                            || ! is_string($value)
                            || ! in_array($value, $allowedTypes, true)
                            || ! class_exists($value)
                        ) {
                            $fail(trans('vgcomment::validation.errors.not_authorized'));
                        }
                    },
                ],
                'commentable_id' => [
                    'nullable',
                    'required_with:commentable_type',
                ],
                'root_id' => [
                    'nullable',
                    "exists:$table,id,parent_id,NULL",
                ],
                'parent_id' => [
                    'nullable',
                    'required_with:root_id',
                    "exists:$table,id",
                ],
                'author_name' => [
                    Rule::requiredIf(! $auth && $allowGuest),
                    'nullable',
                    'string',
                    'max:100',
                ],
                'author_email' => [
                    Rule::requiredIf(! $auth && $allowGuest),
                    'nullable',
                    'email:rfc',
                    'max:255',
                ],
                'author_url' => [
                    'nullable',
                    'url',
                    'max:255',
                ],
                'recaptcha_token' => [
                    Rule::requiredIf((bool) ($this->config['recaptcha'] ?? false)),
                    'nullable',
                    'string',
                ],
            ],
            [],
            [
                'content' => trans('vgcomment::validation.attributes.content'),
            ]
        )->after(function ($validator) use ($request, $auth, $allowGuest) {
            if (! $auth && ! $allowGuest) {
                $validator->errors()->add('content', trans('vgcomment::validation.errors.not_authorized'));
            }

            if (is_string($request->input('content')) && $request->filled('content') && $this->hasDupicate($request->all())) {
                $validator->errors()->add('content', trans('vgcomment::validation.errors.content_duplicate'));
            }

            if ($this->config['recaptcha']) {
                $this->validateReCaptcha($request, $validator);
            }
        });

        return $validator;
    }

    public function updateCommentValidator(Request $request): ValidationIlluminate
    {
        $minLength = $this->config['min_length'];
        $maxLength = $this->config['max_length'];

        return Validator::make(
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
            if (is_string($request->input('content')) && $request->filled('content') && $this->hasDupicate($request->all())) {
                $validator->errors()->add('content', trans('vgcomment::validation.errors.content_duplicate'));
            }
        });
    }

    public function uploadValidator(Request $request): ValidationIlluminate
    {
        $rule = $this->config['upload_rules'];
        $max = $this->config['upload_rules_max'];
        $auth = vgcomment_auth();
        $allowGuest = (bool) $this->config['allow_guests'];

        return Validator::make(
            $request->all(),
            [
                'files' => ['required', 'array', 'max:' . $max],
                'files.*' => $rule,
            ],
            [
                'files.max' => trans('vgcomment::validation.files.max'),
            ],
            [
                'files.*' => trans('vgcomment::validation.attributes.files'),
                'files' => trans('vgcomment::validation.attributes.files'),
            ]
        )->after(function ($validator) use ($request, $auth, $allowGuest) {
            if (! $auth && ! $allowGuest) {
                $validator->errors()->add('files', trans('vgcomment::validation.errors.not_authorized'));
            }

            if (! $this->config['nsfw'] || empty($request->file('files'))) {
                return;
            }

            foreach ($request->file('files') as $file) {
                $isImage = Str::before((string) $file->getMimeType(), '/') === 'image';

                if ($isImage && $this->checkNsfw($file)) {
                    $validator->errors()->add('files', trans('vgcomment::validation.errors.nsfw'));
                }
            }
        });
    }

    protected function hasDupicate(array $request): bool
    {
        $hasDupicate = app(CommentInterface::class)->hasDupicate($request);

        return $this->config['duplicates_check'] ? $hasDupicate : false;
    }

    protected function checkNsfw(mixed $file): bool
    {
        $params = [
            'models' => 'nudity-2.0',
            'api_user' => $this->config['nsfw_api_user'],
            'api_secret' => $this->config['nsfw_api_key'],
        ];

        $response = Http::attach(
            'media',
            $file->get(),
            $file->getClientOriginalName()
        )->post('https://api.sightengine.com/1.0/check.json', $params);

        $data = $response->json();

        if (($data['status'] ?? null) !== 'success') {
            throw new \RuntimeException('Sightengine API error: ' . ($data['error']['message'] ?? 'unknown'));
        }

        return ($data['nudity']['sexual_activity'] ?? 0) > 0.3
            || ($data['nudity']['sexual_display'] ?? 0) > 0.3;
    }

    protected function validateReCaptcha(Request $request, ValidationIlluminate $validator): void
    {
        $token = $request->input('recaptcha_token');

        if (empty($token)) {
            $validator->errors()->add('content', trans('vgcomment::validation.errors.recaptcha_error'));

            return;
        }

        if (! $this->checkReCaptcha($request)) {
            $validator->errors()->add('content', trans('vgcomment::validation.errors.recaptcha_is_bot'));
        }
    }

    protected function checkReCaptcha(Request $request): bool
    {
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $this->config['recaptcha_secret'],
            'response' => $request->input('recaptcha_token'),
            'remoteip' => $request->ip(),
        ]);

        if (! $response->successful()) {
            return false;
        }

        $payload = $response->json();

        if (! ($payload['success'] ?? false)) {
            return false;
        }

        return ($payload['score'] ?? 0) >= 0.5;
    }
}
