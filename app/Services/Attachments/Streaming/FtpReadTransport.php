<?php

declare(strict_types=1);

namespace App\Services\Attachments\Streaming;

interface FtpReadTransport
{
    /** @param resource $target Private staging stream; only retrieve is supported. */
    public function retrieve(string $relativePath, mixed $target, int $maxBytes): void;
}
