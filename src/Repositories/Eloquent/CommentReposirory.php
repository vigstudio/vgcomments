<?php

namespace Vigstudio\VgComment\Repositories\Eloquent;

use Illuminate\Http\Request;
use Vigstudio\VgComment\Models\Comment;
use Vigstudio\VgComment\Repositories\Interface\CommentInterface;
use Illuminate\Database\Eloquent\Builder;
use Vigstudio\VgComment\Facades\FormatterFacade;
use Vigstudio\VgComment\Http\Traits\CommentValidator;
use Vigstudio\VgComment\Http\Traits\ThrottlesPosts;
use Vigstudio\VgComment\Events\CommentCreatedEvent;

class CommentReposirory extends EloquentReposirory implements CommentInterface
{
    use CommentValidator;
    use ThrottlesPosts;

    public function store(array $req): Comment|bool
    {
        $request = $this->makeRequest($req);

        $validator = $this->storeCommentValidator($request);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                session()->push('alert', ['error', $error]);
            }

            return false;
        }

        if (! $request->filled('page_id') && ! $this->commentableExists($request)) {
            session()->push('alert', ['error', trans('vgcomment::validation.errors.not_authorized')]);

            return false;
        }

        if ($this->tooManyAttempts($request)) {
            $seconds = $this->availableIn($request);

            session()->push('alert', ['error', trans('vgcomment::comment.throttle_max_rate', compact('seconds'))]);

            return false;
        }

        try {
            $comment = $this->create($this->protectedRequest($request));

            $comment->status = $this->moderator->determineStatus($comment);
            $comment->save();
            $comment = $comment->fresh();

            $this->incrementAttempts($request);

            event(new CommentCreatedEvent($comment));

            $this->pushAlert($comment);

            return $comment;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getComments(array $req): Builder
    {
        $request = $this->makeRequest($req);

        // Comment lists must stay fresh after create/update; query cache was serving empty pages.
        $query = $this->query()
                        ->dontCache()
                        ->with($this->withRelations($req))
                        ->whereNull('parent_id');

        $commentableExists = $this->commentableExists($request);

        if (! $request->page_id && ! $commentableExists) {
            return $query->where('id', 0);
        }

        $query->when($commentableExists, function ($query) use ($request) {
            $query->where('commentable_id', $request->commentable_id);
            $query->where('commentable_type', $request->commentable_type);
        });

        $query->when($request->page_id, function ($query) use ($request) {
            $query->where('page_id', $request->page_id);
        });

        $this->inStatus($query, [Comment::STATUS_APPROVED]);

        $this->orderComment($query, $request->order);

        return $query;
    }

    public function getCommentsAdmin(array $req): Builder
    {
        $status = $req['status'] ?? 'all';

        // Admin moderation must never serve stale query-cache rows after delete/status changes.
        $query = $this->query()
            ->dontCache()
            ->with([
                'responder',
                'parent' => fn ($builder) => $builder->withTrashed()->dontCache(),
            ])
            ->withCount('reports');

        if ($status === 'deleted') {
            $this->scopeAdminDeleted($query);
        } elseif ($status === 'reported') {
            $query->has('reports');
        } elseif ($status !== 'all' && in_array($status, Comment::STATUSES, true)) {
            $query->where('status', $status);
        }

        $search = trim((string) ($req['q'] ?? ''));
        if ($search !== '') {
            $like = '%'.$search.'%';
            $query->where(function (Builder $builder) use ($like) {
                $builder->where('author_name', 'like', $like)
                    ->orWhere('author_email', 'like', $like)
                    ->orWhere('author_ip', 'like', $like)
                    ->orWhere('content', 'like', $like)
                    ->orWhere('page_id', 'like', $like);
            });
        }

        if (! empty($req['page_id'])) {
            $query->where('page_id', $req['page_id']);
        }

        if (! empty($req['from'])) {
            $query->whereDate('created_at', '>=', $req['from']);
        }

        if (! empty($req['to'])) {
            $query->whereDate('created_at', '<=', $req['to']);
        }

        return $query->orderByDesc('created_at');
    }

    public function getAdminStatusCounts(): array
    {
        $byStatus = $this->query()
            ->dontCache()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->map(fn ($count) => (int) $count)
            ->all();

        $deletedQuery = $this->query()->dontCache();
        $this->scopeAdminDeleted($deletedQuery);

        return [
            'all' => (int) array_sum($byStatus),
            'pending' => $byStatus[Comment::STATUS_PENDING] ?? 0,
            'approved' => $byStatus[Comment::STATUS_APPROVED] ?? 0,
            'spam' => $byStatus[Comment::STATUS_SPAM] ?? 0,
            'trash' => $byStatus[Comment::STATUS_TRASH] ?? 0,
            'reported' => (int) $this->query()->dontCache()->has('reports')->count(),
            'deleted' => (int) $deletedQuery->count(),
        ];
    }

    /**
     * Soft-deleted comments for the admin "deleted" tab.
     * Hides cascade-deleted replies (parent also trashed) so the list is not flooded
     * when a root thread is soft-deleted.
     */
    protected function scopeAdminDeleted(Builder $query): void
    {
        $table = $query->getModel()->getTable();

        $query->onlyTrashed()->where(function (Builder $builder) use ($table) {
            $builder->whereNull($table.'.parent_id')
                ->orWhereNotExists(function ($sub) use ($table) {
                    $sub->selectRaw('1')
                        ->from($table.' as vg_parent_comments')
                        ->whereColumn('vg_parent_comments.id', $table.'.parent_id')
                        ->whereNotNull('vg_parent_comments.deleted_at');
                });
        });
    }

    public function hasDupicate(array $request): bool
    {
        if (! is_string($request['content'] ?? null) || $request['content'] === '') {
            return false;
        }

        $auth = $this->getAuth();

        $duplicate = $this->query()
            ->where('content', FormatterFacade::parse($request['content']))
            ->where('commentable_id', $request['commentable_id'] ?? null)
            ->where('commentable_type', $request['commentable_type'] ?? null)
            ->when($auth, function ($query) use ($auth) {
                return $query->where('responder_id', $auth->getKey())->where('responder_type', get_class($auth));
            })
            ->exists();

        return $duplicate;
    }

    protected function pushAlert(Comment $comment): void
    {
        match ($comment->status) {
            Comment::STATUS_APPROVED => session()->push('alert', ['success', trans('vgcomment::comment.store_success')]),
            Comment::STATUS_PENDING => session()->push('alert', ['alert', trans('vgcomment::comment.store_pending')]),
            Comment::STATUS_SPAM => session()->push('alert', ['error', trans('vgcomment::comment.store_spam')]),
            Comment::STATUS_TRASH => session()->push('alert', ['error', trans('vgcomment::comment.store_trash')]),
        };
    }

    protected function inStatus(Builder $query, array $status): void
    {
        $query->where('status', '!=', Comment::STATUS_TRASH);
        $query->where(function ($query) use ($status) {
            $query->whereIn('status', $status);
        });
    }

    protected function orderComment(Builder $query, string|null $order): void
    {
        match ($order) {
            'oldest' => $query->orderBy('created_at', 'asc'),
            'popular' => $query->orderBy('point', 'desc'),
            default => $query->orderBy('created_at', 'desc'),
        };
    }

    protected function withRelations(array $request): array
    {
        $request = $this->makeRequest($request);

        $hash = vgcomment_page_hash($request->page_id, $request->commentable_id, $request->commentable_type);

        return [
            'reactions' => function ($query) use ($hash) {
                return $query->cacheTags(['vigcomment_reaction_releation_' . $hash]);
            },
            'parent' => function ($query) use ($hash) {
                return $query->cacheTags(['vigcomment_reaction_parent_' . $hash]);
            },
            'files' => function ($query) use ($hash) {
                return $query->cacheTags(['vigcomment_reaction_files_' . $hash]);
            },
            'responder' => function ($query) use ($hash) {
                return $query->cacheTags(['vigcomment_reaction_responder_' . $hash]);
            },
            'replies' => function ($query) use ($hash) {
                return $query->where('status', Comment::STATUS_APPROVED)
                            ->cacheTags(['vigcomment_reaction_replies_' . $hash]);
            },
        ];
    }

    protected function commentableExists(Request $request)
    {
        $id = $request->commentable_id;
        $type = $request->commentable_type;
        $allowedTypes = config('vgcomment.allowed_commentable_types', []);

        if (blank($type) || blank($id) || ! is_string($type) || ! class_exists($type)) {
            return false;
        }

        if (empty($allowedTypes) || ! in_array($type, $allowedTypes, true)) {
            return false;
        }

        $model = new $type();

        return ! is_null($model->newQuery()->find($id, [$model->getKeyName()]));
    }

    protected function protectedRequest(Request $request): array
    {
        $auth = $this->getAuth();

        $name = ! empty($this->config['user_column_name']) ? $this->config['user_column_name'] : 'name';
        $email = ! empty($this->config['user_column_email']) ? $this->config['user_column_email'] : 'email';
        $url = ! empty($this->config['user_column_url']) ? $this->config['user_column_url'] : 'url';

        $mergeRequest = [
            'responder_type' => $auth ? get_class($auth) : null,
            'responder_id' => $auth ? $auth->getKey() : null,
            'author_name' => $auth ? $auth->$name : $request->author_name,
            'author_email' => $auth ? $auth->$email : $request->author_email,
            'author_url' => $auth ? $auth->$url : $request->author_url,
        ];

        $input = $request->merge($mergeRequest)->only([
            'page_id',
            'commentable_type',
            'commentable_id',
            'author_url',
            'content',
            'root_id',
            'parent_id',
            'author_ip',
            'user_agent',
            'responder_type',
            'responder_id',
            'author_name',
            'author_email',
            'permalink',
        ]);

        foreach (['commentable_type', 'commentable_id', 'root_id', 'parent_id', 'page_id', 'author_url'] as $key) {
            if (($input[$key] ?? null) === '' || ($input[$key] ?? null) === 'null') {
                $input[$key] = null;
            }
        }

        return $input;
    }
}
