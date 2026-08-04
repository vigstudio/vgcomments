<?php

namespace Vigstudio\VgComment\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Vigstudio\VgComment\Http\Resources\CommentResource;
use Vigstudio\VgComment\Http\Resources\FileResource;
use Vigstudio\VgComment\Http\Traits\CommentValidator;
use Vigstudio\VgComment\Http\Traits\ThrottlesPosts;
use Vigstudio\VgComment\Models\Comment;
use Vigstudio\VgComment\Models\Reaction;
use Vigstudio\VgComment\Repositories\Interface\CommentInterface;
use Vigstudio\VgComment\Repositories\Interface\FileInterface;
use Vigstudio\VgComment\Repositories\Interface\ReactionInterface;

class CommentService
{
    use AuthorizesRequests;
    use CommentValidator;
    use ThrottlesPosts;

    protected array $config;

    protected Request $request;

    protected CommentInterface $commentRepository;

    protected FileInterface $fileRepository;

    protected ReactionInterface $reactionRepository;

    public function __construct(
        CommentInterface $commentRepository,
        FileInterface $fileRepository,
        ReactionInterface $reactionRepository,
        Request $request
    ) {
        $this->config = vgcomment_config();
        $this->request = $request;
        $this->commentRepository = $commentRepository;
        $this->fileRepository = $fileRepository;
        $this->reactionRepository = $reactionRepository;
    }

    public function getAuth(): Authenticatable|bool
    {
        return GetAuthenticatableService::get();
    }

    public function get(array $req = [], bool $jsonResource = true): JsonResource|LengthAwarePaginator
    {
        $comments = $this->commentRepository
            ->getComments($req)
            ->paginate(10, ['*'], 'vgcomment_page');

        $comments->getCollection()->each(fn (Comment $comment) => $comment->nestReplies());

        if ($jsonResource) {
            return CommentResource::collection($comments);
        }

        return $comments;
    }

    public function getAdmin(array $req = [], bool $jsonResource = false): JsonResource|LengthAwarePaginator
    {
        $comments = $this->commentRepository
            ->getCommentsAdmin($req)
            ->paginate(20, ['*'], 'vgcomment_page')
            ->withQueryString();

        if ($jsonResource) {
            return CommentResource::collection($comments);
        }

        return $comments;
    }

    public function getAdminStatusCounts(): array
    {
        return $this->commentRepository->getAdminStatusCounts();
    }

    public function bulkUpdateStatus(array $ids, string $status): int
    {
        if (! in_array($status, Comment::STATUSES, true)) {
            return 0;
        }

        $ids = $this->normalizeIds($ids);

        if ($ids === []) {
            return 0;
        }

        $count = 0;

        // withTrashed: status actions from the deleted tab must restore + update.
        // Per-model update fires query-cache flush; mass update() does not.
        Comment::withTrashed()->whereIn('id', $ids)->each(function (Comment $comment) use ($status, &$count) {
            if ($comment->trashed()) {
                $this->restoreCommentTree($comment);
            }

            $comment->update(['status' => $status]);
            $count++;
        });

        Comment::flushQueryCache();

        return $count;
    }

    public function bulkDelete(array $ids): int
    {
        $ids = $this->normalizeIds($ids);

        if ($ids === []) {
            return 0;
        }

        $count = 0;

        Comment::query()->whereIn('id', $ids)->each(function (Comment $comment) use (&$count) {
            $this->softDeleteComment($comment);
            $count++;
        });

        Comment::flushQueryCache();

        return $count;
    }

    public function bulkRestore(array $ids): int
    {
        $ids = $this->normalizeIds($ids);

        if ($ids === []) {
            return 0;
        }

        $count = 0;

        Comment::onlyTrashed()->whereIn('id', $ids)->each(function (Comment $comment) use (&$count) {
            $this->restoreCommentTree($comment, true);
            $count++;
        });

        Comment::flushQueryCache();

        return $count;
    }

    public function softDeleteComment(Comment $comment): void
    {
        if (is_null($comment->parent_id)) {
            // Flat root_id relation covers the whole thread.
            $comment->replies()->delete();
        } else {
            $this->softDeleteDescendants((int) $comment->id);
        }

        $comment->delete();
    }

    public function restoreCommentTree(Comment $comment, bool $approve = false): void
    {
        if (! $comment->trashed()) {
            if ($approve) {
                $comment->update(['status' => Comment::STATUS_APPROVED]);
            }

            return;
        }

        $comment->restore();

        if (is_null($comment->parent_id)) {
            $comment->replies()->onlyTrashed()->restore();
        }

        if ($approve) {
            $comment->refresh();
            $comment->update(['status' => Comment::STATUS_APPROVED]);

            if (is_null($comment->parent_id)) {
                Comment::query()
                    ->where('root_id', $comment->id)
                    ->update(['status' => Comment::STATUS_APPROVED]);
            }
        }
    }

    protected function softDeleteDescendants(int $parentId): void
    {
        Comment::query()->where('parent_id', $parentId)->each(function (Comment $child) {
            $this->softDeleteDescendants((int) $child->id);
            $child->delete();
        });
    }

    public function bulkForceDelete(array $ids): int
    {
        $ids = $this->normalizeIds($ids);

        if ($ids === []) {
            return 0;
        }

        $count = 0;

        Comment::onlyTrashed()->whereIn('id', $ids)->each(function (Comment $comment) use (&$count) {
            if (is_null($comment->parent_id)) {
                $comment->replies()->withTrashed()->forceDelete();
            } else {
                Comment::withTrashed()->where('parent_id', $comment->id)->forceDelete();
            }

            $comment->reactions()->forceDelete();
            $comment->reports()->forceDelete();
            $comment->files()->forceDelete();
            $comment->forceDelete();
            $count++;
        });

        Comment::flushQueryCache();

        return $count;
    }

    protected function normalizeIds(array $ids): array
    {
        return collect($ids)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    public function findById(int $id): mixed
    {
        return $this->commentRepository->find($id);
    }

    public function findByUuid(string $uuid): ?Comment
    {
        return $this->commentRepository->findByUuid($uuid);
    }

    public function store(array $req): Comment|false
    {
        $request = $this->mergeRequest($this->request->merge($req));

        return $this->commentRepository->store($request) ?: false;
    }

    public function update(array $req, string $uuid): bool
    {
        $comment = $this->commentRepository->findByUuid($uuid);

        if (! $comment || ! vgcomment_policy($comment->id, 'update')) {
            session()->push('alert', ['error', trans('vgcomment::validation.errors.not_authorized')]);

            return false;
        }

        $input = collect($req)->only('content')->all();
        $result = $comment->update($input);

        Comment::flushQueryCache();

        session()->push('alert', ['success', trans('vgcomment::comment.update_success')]);

        return (bool) $result;
    }

    public function delete(string $uuid): bool
    {
        $comment = $this->commentRepository->findByUuid($uuid);

        if (! $comment || ! vgcomment_policy($comment->id, 'delete')) {
            session()->push('alert', ['error', trans('vgcomment::validation.errors.not_authorized')]);

            return false;
        }

        $result = $this->commentRepository->delete($comment->id);

        Comment::flushQueryCache();

        session()->push('alert', ['success', trans('vgcomment::comment.delete_success')]);

        return (bool) $result;
    }

    public function upload($files): JsonResource|false
    {
        if (! $this->getAuth() && ! ($this->config['allow_guests'] ?? false)) {
            session()->push('alert', ['error', trans('vgcomment::validation.errors.not_authorized')]);

            return false;
        }

        $filesResource = $this->fileRepository->upload($files);

        return $filesResource ? FileResource::collection($filesResource) : false;
    }

    public function registerFilesForComment(Comment $comment, array $files): bool
    {
        return $this->fileRepository->registerFilesForComment($comment, $files);
    }

    public function reaction(string $uuid, string $type): bool
    {
        $this->assertReactionType($type);

        $comment = $this->commentRepository->findByUuid($uuid);

        if (! $comment) {
            session()->push('alert', ['error', trans('vgcomment::validation.errors.not_authorized')]);

            return false;
        }

        $auth = $this->getAuth();

        if ($auth) {
            $auth->react($comment, $type);
            $this->flushReactionCaches($comment);

            return true;
        }

        if (! ($this->config['allow_guests'] ?? false)) {
            session()->push('alert', ['error', trans('vgcomment::validation.errors.not_authorized')]);

            return false;
        }

        $ok = $this->guestReact($comment, $type);
        $this->flushReactionCaches($comment);

        return $ok;
    }

    public function deleteReaction(string $uuid, string $type): bool
    {
        $this->assertReactionType($type);

        $comment = $this->commentRepository->findByUuid($uuid);

        if (! $comment) {
            session()->push('alert', ['error', trans('vgcomment::validation.errors.not_authorized')]);

            return false;
        }

        $auth = $this->getAuth();

        if ($auth) {
            $auth->unReact($comment, $type);
            $this->flushReactionCaches($comment);

            return true;
        }

        if (! ($this->config['allow_guests'] ?? false)) {
            session()->push('alert', ['error', trans('vgcomment::validation.errors.not_authorized')]);

            return false;
        }

        $ok = $this->guestUnReact($comment, $type);
        $this->flushReactionCaches($comment);

        return $ok;
    }

    protected function flushReactionCaches(?Comment $comment = null): void
    {
        Comment::flushQueryCache();
        Reaction::flushQueryCache();

        if (! $comment) {
            return;
        }

        $hash = vgcomment_page_hash(
            $comment->page_id,
            $comment->commentable_id,
            $comment->commentable_type
        );

        $tags = [
            'vigcomment_reaction_releation_' . $hash,
            'vigcomment_reaction_parent_' . $hash,
            'vigcomment_reaction_files_' . $hash,
            'vigcomment_reaction_responder_' . $hash,
            'vigcomment_reaction_replies_' . $hash,
        ];

        Comment::flushQueryCache($tags);
        Reaction::flushQueryCache($tags);
    }

    protected function guestReact(Comment $comment, string $type): bool
    {
        [$reactableType, $reactableId] = $this->guestReactorIdentity();

        $existing = Reaction::query()
            ->where('comment_uuid', $comment->getUuid())
            ->where('reactable_type', $reactableType)
            ->where('reactable_id', $reactableId)
            ->first();

        if ($existing && $existing->type === $type) {
            return true;
        }

        if ($existing) {
            $existing->delete();
        }

        Reaction::query()->create([
            'comment_id' => $comment->getKey(),
            'comment_uuid' => $comment->getUuid(),
            'type' => $type,
            'reactable_type' => $reactableType,
            'reactable_id' => $reactableId,
        ]);

        return true;
    }

    protected function guestUnReact(Comment $comment, string $type): bool
    {
        [$reactableType, $reactableId] = $this->guestReactorIdentity();

        $existing = Reaction::query()
            ->where('comment_uuid', $comment->getUuid())
            ->where('reactable_type', $reactableType)
            ->where('reactable_id', $reactableId)
            ->where('type', $type)
            ->first();

        if (! $existing) {
            return false;
        }

        return (bool) $existing->delete();
    }

    /**
     * @return array{0: string, 1: int}
     */
    protected function guestReactorIdentity(): array
    {
        $token = session()->get('vgcomment.guest_reactor');

        if (! is_string($token) || $token === '') {
            $token = (string) Str::uuid();
            session()->put('vgcomment.guest_reactor', $token);
        }

        return ['vgcomment_guest', (int) sprintf('%u', crc32($token))];
    }

    public function report(string $uuid): bool
    {
        $comment = $this->commentRepository->findByUuid($uuid);
        $auth = $this->getAuth();

        if (! $comment || ! $auth || ! vgcomment_policy($comment->id, 'report')) {
            session()->push('alert', ['error', trans('vgcomment::validation.errors.not_authorized')]);

            return false;
        }

        $auth->report($comment);

        $status = $this->config['report_status'];
        $maxReports = $this->config['max_reports'];

        if ($maxReports && $comment->reports()->count() >= $maxReports) {
            $comment->update(['status' => $status]);
        }

        session()->push('alert', ['success', trans('vgcomment::comment.report_success')]);

        return true;
    }

    protected function assertReactionType(string $type): void
    {
        // Any unicode emoji / ZWJ sequence that fits the string column (max 32).
        // config('vgcomment.reaction_types') is only a UI quick-suggestion list.
        validator(['type' => $type], [
            'type' => ['required', 'string', 'min:1', 'max:32'],
        ])->validate();
    }

    protected function mergeRequest(Request $request): array
    {
        $auth = $this->getAuth();

        $name = ! empty($this->config['user_column_name']) ? $this->config['user_column_name'] : 'name';
        $email = ! empty($this->config['user_column_email']) ? $this->config['user_column_email'] : 'email';
        $url = ! empty($this->config['user_column_url']) ? $this->config['user_column_url'] : 'url';

        $authorName = $auth ? $auth->$name : $request->input('author_name');
        $authorEmail = $auth ? $auth->$email : $request->input('author_email');
        $authorUrl = $auth ? $auth->$url : $request->input('author_url');

        $request->session()->put('author.name', $authorName);
        $request->session()->put('author.email', $authorEmail);
        $request->session()->put('author.url', $authorUrl);

        $mergeRequest = [
            'author_ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
            'responder_type' => $auth ? get_class($auth) : null,
            'responder_id' => $auth ? $auth->getKey() : null,
            'author_name' => $authorName,
            'author_email' => $authorEmail,
            'author_url' => $authorUrl,
            'permalink' => $request->headers->get('referer'),
        ];

        return $request->merge($mergeRequest)->all();
    }
}
