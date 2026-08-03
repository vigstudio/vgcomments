<?php

namespace Vigstudio\VgComment\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Vigstudio\VgComment\Http\Resources\CommentResource;
use Vigstudio\VgComment\Http\Resources\FileResource;
use Vigstudio\VgComment\Http\Traits\CommentValidator;
use Vigstudio\VgComment\Http\Traits\ThrottlesPosts;
use Vigstudio\VgComment\Models\Comment;
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

        if ($jsonResource) {
            return CommentResource::collection($comments);
        }

        return $comments;
    }

    public function getAdmin(array $req = [], bool $jsonResource = true): JsonResource|LengthAwarePaginator
    {
        $comments = $this->commentRepository
            ->getCommentsAdmin($req)
            ->paginate(10, ['*'], 'vgcomment_page');

        if ($jsonResource) {
            return CommentResource::collection($comments);
        }

        return $comments;
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

        if (! $comment || ! $this->getAuth()) {
            session()->push('alert', ['error', trans('vgcomment::validation.errors.not_authorized')]);

            return false;
        }

        $this->getAuth()->react($comment, $type);

        return true;
    }

    public function deleteReaction(string $uuid, string $type): bool
    {
        $this->assertReactionType($type);

        $comment = $this->commentRepository->findByUuid($uuid);

        if (! $comment || ! $this->getAuth()) {
            session()->push('alert', ['error', trans('vgcomment::validation.errors.not_authorized')]);

            return false;
        }

        $this->getAuth()->unReact($comment, $type);

        return true;
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
        $allowed = $this->config['reaction_types'] ?? ['👍', '❤️', '😄', '😮', '😢', '😡'];

        validator(['type' => $type], [
            'type' => ['required', 'string', Rule::in($allowed)],
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
