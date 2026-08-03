<?php

namespace Vigstudio\VgComment\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Vigstudio\VgComment\Facades\CommentServiceFacade;
use Vigstudio\VgComment\Http\Resources\CommentResource;
use Vigstudio\VgComment\Http\Resources\FileResource;
use Vigstudio\VgComment\Services\ValidatorService;

class CommentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'page_id' => ['nullable', 'string', 'max:255'],
            'commentable_type' => ['nullable', 'string', 'max:255'],
            'commentable_id' => ['nullable'],
            'order' => ['nullable', Rule::in(['latest', 'oldest', 'popular'])],
            'vgcomment_page' => ['nullable', 'integer', 'min:1'],
        ]);

        $comments = CommentServiceFacade::get($request->only([
            'page_id',
            'commentable_type',
            'commentable_id',
            'order',
        ]));

        return response()->json($comments);
    }

    public function store(Request $request, ValidatorService $validatorService): JsonResponse
    {
        $validatorService->storeCommentValidator($request)->validate();

        $comment = CommentServiceFacade::store($request->only([
            'content',
            'page_id',
            'commentable_type',
            'commentable_id',
            'author_name',
            'author_email',
            'author_url',
            'root_id',
            'parent_id',
            'recaptcha_token',
        ]));

        if (! $comment) {
            return $this->alertErrorResponse(422);
        }

        if ($request->filled('attachments')) {
            CommentServiceFacade::registerFilesForComment($comment, $request->input('attachments', []));
        }

        return response()->json([
            'message' => trans('vgcomment::comment.store_success'),
            'data' => new CommentResource($comment->load(['replies', 'files', 'reactions', 'responder', 'parent'])),
        ], 201);
    }

    public function update(Request $request, string $uuid, ValidatorService $validatorService): JsonResponse
    {
        $validatorService->updateCommentValidator($request)->validate();

        $updated = CommentServiceFacade::update($request->only('content'), $uuid);

        if (! $updated) {
            return $this->alertErrorResponse(403);
        }

        $comment = CommentServiceFacade::findByUuid($uuid);

        return response()->json([
            'message' => trans('vgcomment::comment.update_success'),
            'data' => $comment ? new CommentResource($comment) : null,
        ]);
    }

    public function destroy(string $uuid): JsonResponse
    {
        $deleted = CommentServiceFacade::delete($uuid);

        if (! $deleted) {
            return $this->alertErrorResponse(403);
        }

        return response()->json([
            'message' => trans('vgcomment::comment.delete_success'),
        ]);
    }

    public function react(Request $request, string $uuid): JsonResponse
    {
        $request->validate([
            'type' => ['required', 'string', Rule::in(config('vgcomment.reaction_types', ['👍', '❤️', '😄', '😮', '😢', '😡']))],
        ]);

        $ok = CommentServiceFacade::reaction($uuid, $request->input('type'));

        if (! $ok) {
            return $this->alertErrorResponse(403);
        }

        return response()->json(['message' => 'ok']);
    }

    public function unreact(Request $request, string $uuid): JsonResponse
    {
        $request->validate([
            'type' => ['required', 'string', Rule::in(config('vgcomment.reaction_types', ['👍', '❤️', '😄', '😮', '😢', '😡']))],
        ]);

        $ok = CommentServiceFacade::deleteReaction($uuid, $request->input('type'));

        if (! $ok) {
            return $this->alertErrorResponse(403);
        }

        return response()->json(['message' => 'ok']);
    }

    public function report(string $uuid): JsonResponse
    {
        $ok = CommentServiceFacade::report($uuid);

        if (! $ok) {
            return $this->alertErrorResponse(403);
        }

        return response()->json([
            'message' => trans('vgcomment::comment.report_success'),
        ]);
    }

    public function upload(Request $request, ValidatorService $validatorService): JsonResponse
    {
        $request->merge([
            'files' => $request->file('files', []),
        ]);

        $validatorService->uploadValidator($request)->validate();

        $files = CommentServiceFacade::upload($request->file('files', []));

        if (! $files) {
            return $this->alertErrorResponse(422);
        }

        return response()->json([
            'message' => trans('vgcomment::comment.upload_success'),
            'data' => FileResource::collection($files),
        ], 201);
    }

    protected function alertErrorResponse(int $status): JsonResponse
    {
        $alerts = collect(session()->pull('alert', []));
        $message = $alerts->pluck(1)->filter()->first() ?: trans('vgcomment::validation.errors.not_authorized');

        return response()->json([
            'message' => $message,
            'errors' => $alerts->map(fn ($alert) => [
                'type' => $alert[0] ?? 'error',
                'message' => $alert[1] ?? '',
            ])->values(),
        ], $status);
    }
}
