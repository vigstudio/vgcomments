<?php

namespace Vigstudio\VgComment\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Vigstudio\VgComment\Facades\CommentServiceFacade;
use Vigstudio\VgComment\Facades\FormatterFacade;
use Vigstudio\VgComment\Http\Resources\CommentResource;
use Vigstudio\VgComment\Models\Comment;
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

        $filters = $this->normalizeCommentFilters($request->only([
            'page_id',
            'commentable_type',
            'commentable_id',
            'order',
        ]));

        $comments = CommentServiceFacade::get($filters);

        return $comments->response();
    }

    public function store(Request $request, ValidatorService $validatorService): JsonResponse
    {
        $payload = $this->normalizeCommentFilters($request->only([
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

        $request->merge($payload);

        $validatorService->storeCommentValidator($request)->validate();

        $comment = CommentServiceFacade::store($payload);

        if (! $comment) {
            return $this->alertErrorResponse(422);
        }

        if ($request->filled('attachments')) {
            CommentServiceFacade::registerFilesForComment($comment, $request->input('attachments', []));
        }

        $comment->load(['replies', 'files', 'reactions', 'votes', 'responder', 'parent']);
        $comment->nestReplies();

        $alerts = collect(session()->pull('alert', []));
        $message = $alerts->pluck(1)->filter()->first() ?: match ($comment->status) {
            Comment::STATUS_PENDING => trans('vgcomment::comment.store_pending'),
            Comment::STATUS_SPAM => trans('vgcomment::comment.store_spam'),
            Comment::STATUS_TRASH => trans('vgcomment::comment.store_trash'),
            default => trans('vgcomment::comment.store_success'),
        };

        return response()->json([
            'message' => $message,
            'data' => new CommentResource($comment),
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
            'type' => ['required', 'string', 'min:1', 'max:32'],
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
            'type' => ['required', 'string', 'min:1', 'max:32'],
        ]);

        $ok = CommentServiceFacade::deleteReaction($uuid, $request->input('type'));

        if (! $ok) {
            return $this->alertErrorResponse(403);
        }

        return response()->json(['message' => 'ok']);
    }

    public function vote(Request $request, string $uuid): JsonResponse
    {
        $request->validate([
            'value' => ['required', 'integer', Rule::in([1, -1])],
        ]);

        $summary = CommentServiceFacade::vote($uuid, (int) $request->input('value'));

        if ($summary === false) {
            return $this->alertErrorResponse(403);
        }

        return response()->json([
            'message' => 'ok',
            'data' => $summary,
        ]);
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

    public function preview(Request $request): JsonResponse
    {
        $content = (string) $request->input('content', '');
        $parsed = FormatterFacade::parse($content);
        $html = FormatterFacade::render($parsed) ?: '';

        return response()->json([
            'html' => $html,
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

    /**
     * Drop blank morph / id keys so filters and validation stay consistent.
     */
    protected function normalizeCommentFilters(array $payload): array
    {
        foreach (['commentable_id', 'commentable_type', 'root_id', 'parent_id', 'page_id', 'author_url'] as $key) {
            if (! array_key_exists($key, $payload)) {
                continue;
            }

            if ($payload[$key] === '' || $payload[$key] === 'null' || $payload[$key] === null) {
                unset($payload[$key]);
            }
        }

        return $payload;
    }
}
