<?php

namespace Vigstudio\VgComment\Services;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Config;
use Vigstudio\VgComment\Http\Resources\FileResource;
use Vigstudio\VgComment\Http\Resources\CommentResource;
use Vigstudio\VgComment\Http\Traits\CommentValidator;
use Vigstudio\VgComment\Http\Traits\ThrottlesPosts;
use Vigstudio\VgComment\Models\Comment;
use Vigstudio\VgComment\Repositories\Interface\CommentInterface;
use Vigstudio\VgComment\Repositories\Interface\FileInterface;
use Vigstudio\VgComment\Repositories\Interface\ReactionInterface;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Pagination\LengthAwarePaginator;

class CommentService
{
    use ThrottlesPosts;
    use CommentValidator;
    use AuthorizesRequests;

    protected $config;

    protected $request;

    protected CommentInterface $commentRepository;

    protected FileInterface $fileRepository;

    protected ReactionInterface $reactionRepository;

    public function __construct(
        CommentInterface $commentRepository,
        FileInterface $fileRepository,
        ReactionInterface $reactionRepository,
        Request $request
    ) {
        $this->config = Config::get('vgcomment');
        $this->request = $request;

        $this->commentRepository = $commentRepository;
        $this->fileRepository = $fileRepository;
        $this->reactionRepository = $reactionRepository;
    }

    /**
     * Author by Vigstudio
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|bool
     */
    public function getAuth(): Authenticatable|bool
    {
        return GetAuthenticatableService::get();
    }

    /**
     * Author by Vigstudio
     *
     * @param array $req
     * @param bool $jsonResource
     * @return \Illuminate\Http\Resources\Json\JsonResource|\Illuminate\Pagination\LengthAwarePaginator
     */
    public function get(array $req = [], $jsonResource = true): JsonResource|LengthAwarePaginator
    {
        $comments = $this->commentRepository
                        ->getComments($req)
                        ->paginate($perPage = 10, $columns = ['*'], $pageName = 'vgcomment_page');

        if ($jsonResource) {
            return CommentResource::collection($comments);
        }

        return $comments;
    }

    /**
     * Author by Vigstudio
     *
     * @param array $req
     * @param bool $jsonResource
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    public function getAdmin(array $req = [], bool $jsonResource = true): JsonResource
    {
        $comments = $this->commentRepository
                        ->getCommentsAdmin($req)
                        ->paginate($perPage = 10, $columns = ['*'], $pageName = 'vgcomment_page');

        if ($jsonResource) {
            return CommentResource::collection($comments);
        }

        return $comments;
    }

    /**
     * Author by Vigstudio
     *
     * @param int $id
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    public function findById(int $id): mixed
    {
        $comments = $this->commentRepository->find($id);

        return $comments;
    }

    /**
     * Author by Vigstudio
     *
     * @param string $uuid
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    public function store(array $req): Comment|bool
    {
        $request = $this->mergeRequest($this->request->merge($req));
        $comment = $this->commentRepository->store($request);

        return $comment;
    }

    /**
     * Author by Vigstudio
     *
     * @param array $req
     * @param string $uuid
     * @return bool
     */
    public function update(array $req, string $uuid): bool
    {
        $request = $this->request->merge($req);

        $comment = $this->commentRepository->findByUuid($uuid);

        if (! vgcomment_policy($comment->id, 'update')) {
            session()->push('alert', ['error', trans('vgcomment::validation.errors.not_authorized')]);

            return false;
        }

        $input = $request->only('content');

        $result =  $comment->update($input);

        session()->push('alert', ['success', trans('vgcomment::comment.update_success')]);

        return $result;
    }

    /**
     * Author by Vigstudio
     *
     * @param string $uuid
     * @return bool
     */
    public function delete(string $uuid): bool
    {
        $comment = $this->commentRepository->findByUuid($uuid);

        if (! vgcomment_policy($comment->id, 'delete')) {
            session()->push('alert', ['error', trans('vgcomment::validation.errors.not_authorized')]);

            return false;
        }

        $result =  $this->commentRepository->delete($comment->id);

        session()->push('alert', ['success', trans('vgcomment::comment.delete_success')]);

        return $result;
    }

    /**
     * Author by Vigstudio
     *
     * @param $files
     * @return \Illuminate\Http\Resources\Json\JsonResource|bool
     */
    public function upload($files): JsonResource|bool
    {
        $filesResource = $this->fileRepository->upload($files);

        return $filesResource ? FileResource::collection($filesResource) : false;
    }

      /**
     * Author by Vigstudio
     *
     * @param \Vigstudio\VgComment\Models\Comment $comment
     * @param array $files
     * @return bool
     */
    public function registerFilesForComment(Comment $comment, array $files): bool
    {
        return $this->fileRepository->registerFilesForComment($comment, $files);
    }

    /**
     * Author by Vigstudio
     *
     * @param $uuid
     * @return bool
     */
    public function reaction(string $uuid, string $type): bool
    {
        $comment = $this->commentRepository->findByUuid($uuid);

        if ($this->getAuth()) {
            $this->getAuth()->react($comment, $type);

            return true;
        }
        session()->push('alert', ['error', trans('vgcomment::validation.errors.not_authorized')]);

        return false;
    }

    /**
     * Author by Vigstudio
     *
     * @param $uuid
     * @return bool
     */
    public function deleteReaction(string $uuid, string $type): bool
    {
        $comment = $this->commentRepository->findByUuid($uuid);

        if ($this->getAuth()) {
            $this->getAuth()->unReact($comment, $type);

            return true;
        }
        session()->push('alert', ['error', trans('vgcomment::validation.errors.not_authorized')]);

        return false;
    }

    /**
     * Author by Vigstudio
     *
     * @param $uuid
     * @return bool
     */
    public function report(string $uuid): bool
    {
        $comment = $this->commentRepository->findByUuid($uuid);

        $this->getAuth()->report($comment);

        $status = $this->config['report_status'];
        $maxReports = $this->config['max_reports'];

        if ($maxReports && $comment->reports()->count() >= $maxReports) {
            $comment->update(['status' => $status]);
        }

        session()->push('alert', ['success', trans('vgcomment::comment.report_success')]);

        return true;
    }

     /**
     * Author by Vigstudio
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    protected function mergeRequest(Request $request): array
    {
        $auth = $this->getAuth();

        $name = ! empty($this->config['user_column_name']) ? $this->config['user_column_name'] : 'name';
        $email = ! empty($this->config['user_column_email']) ? $this->config['user_column_email'] : 'email';
        $url = ! empty($this->config['user_column_url']) ? $this->config['user_column_url'] : 'url';

        $author_name = $auth ? $auth->$name : $request->author_name;
        $author_email = $auth ? $auth->$email : $request->author_email;
        $author_url = $auth ? $auth->$url : $request->author_url;

        $request->session()->put('author.name', $author_name);
        $request->session()->put('author.email', $author_email);
        $request->session()->put('author.url', $author_url);

        $mergeRequest = [
            'author_ip' => $request->server('REMOTE_ADDR'),
            'user_agent' => $request->server('HTTP_USER_AGENT'),
            'responder_type' => $auth ? get_class($auth) : null,
            'responder_id' => $auth ? $auth->getKey() : null,
            'author_name' => $author_name,
            'author_email' => $author_email,
            'author_url' => $author_url,
            'permalink' => $request->server('HTTP_REFERER'),
        ];

        $input = $request->merge($mergeRequest);

        return $input->all();
    }
}
