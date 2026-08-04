<?php

namespace Vigstudio\VgComment\Support;

use Throwable;
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

        // Logged-in authors already cleared account checks; shared/proxy IPs
        // (e.g. Cloudflare edges mis-attributed as client IP) should not hide their posts.
        if ($auth || $comment->responder_id) {
            if ($this->config['moderation'] ?? false) {
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

            return Comment::STATUS_APPROVED;
        }

        if ($this->config['moderation'] ?? false) {
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

        foreach ($this->config[$type] ?? [] as $key) {
            if (empty($key)) {
                continue;
            }

            foreach ($fields as $field) {
                if (is_string($field) && preg_match('/\b' . preg_quote($key, '/') . '\b/u', $field)) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function hasTooManyLinks(Comment $comment)
    {
        if (! ($this->config['max_links'] ?? null)) {
            return false;
        }

        $xml = $this->formatter->parse($comment->content);

        $html = $this->formatter->render($xml);

        $found = preg_match_all('/<a [^>]*href/i', $html);

        return $found >= $this->config['max_links'];
    }

    protected function isSpam(Comment $comment): bool
    {
        if (! ($this->config['stopforumspam'] ?? true)) {
            return false;
        }

        $email = trim((string) $comment->author_email);
        $ip = trim((string) $comment->author_ip);

        if ($email === '' && $ip === '') {
            return false;
        }

        // Never treat private/reserved addresses as StopForumSpam signals.
        if ($ip !== '' && $this->isNonPublicIp($ip)) {
            $ip = '';
        }

        $frequency = (int) ($this->config['stopforumspam_frequency'] ?? 10);

        try {
            $isSpamEmail = false;
            $isSpamIp = false;

            if ($email !== '') {
                $isSpamEmail = (new StopForumSpam())
                    ->setEmail($email)
                    ->setFrequency($frequency)
                    ->isSpamEmail();
            }

            if ($ip !== '') {
                $isSpamIp = (new StopForumSpam())
                    ->setIp($ip)
                    ->setFrequency($frequency)
                    ->isSpamIp();
            }

            return $isSpamEmail || $isSpamIp;
        } catch (Throwable $e) {
            report($e);

            return false;
        }
    }

    protected function isNonPublicIp(string $ip): bool
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6) === false) {
            return true;
        }

        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }
}
