<?php

namespace Vigstudio\VgComment\Support;

use Vigstudio\VgComment\Models\Comment;
use Vigstudio\VgComment\Repositories\ContractsInterface\CommentFormatterInterface;
use Vigstudio\VgComment\Repositories\ContractsInterface\ModeratorInterface;
use Vigstudio\VgComment\Services\GetAuthenticatableService;
use nickurt\StopForumSpam\StopForumSpam;

class Moderator implements ModeratorInterface
{
    protected $config;

    protected $formatter;

    public function __construct(array $config, CommentFormatterInterface $formatter)
    {
        $this->config = $config;
        $this->formatter = $formatter;
    }

    public function determineStatus(Comment $comment): string
    {
        $auth = GetAuthenticatableService::get();

        if ($auth && $auth->can('moderate', Comment::class)) {
            return Comment::STATUS_APPROVED;
        }

        if ($this->config['moderation']) {
            return Comment::STATUS_PENDING;
        }

        if ($this->contains($comment, 'blacklist_keys')) {
            return Comment::STATUS_SPAM;
        }

        if ($this->contains($comment, 'moderation_keys')) {
            return Comment::STATUS_PENDING;
        }

        if ($this->hasTooManyLinks($comment)) {
            return Comment::STATUS_PENDING;
        }

        if ($this->isSpam($comment)) {
            return Comment::STATUS_SPAM;
        }

        return Comment::STATUS_APPROVED;
    }

    protected function contains(Comment $comment, string $type): bool
    {
        $fields = $comment->toArray();

        foreach ($this->config[$type] as $key) {
            if (empty($key)) {
                continue;
            }

            foreach ($fields as $field) {
                if (is_string($field) && preg_match('/\b' . $key . '\b/', $field)) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function hasTooManyLinks($comment)
    {
        if (! $this->config['max_links']) {
            return false;
        }

        $xml = $this->formatter->parse($comment->content);

        $html = $this->formatter->render($xml);

        $found = preg_match_all('/<a [^>]*href/i', $html);

        return $found >= $this->config['max_links'];
    }

    protected function isSpam($comment)
    {
        $isSpamEmail = (new StopForumSpam())->setEmail($comment->author_email)->isSpamEmail();
        $isSpamIp = (new StopForumSpam())->setIp($comment->author_ip)->isSpamIp();

        return $isSpamEmail || $isSpamIp;
    }
}
