<?php

namespace Vigstudio\VgComment\Repositories\ContractsInterface;

interface CommentFormatterInterface
{
    public function parse(string $text): string;

    public function unparse(string $xml): string;

    public function render(string $xml): string;

    public function flush(): void;
}
