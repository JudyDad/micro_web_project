<?php

declare(strict_types=1);

namespace App\Services\Attachments\Streaming;

interface ReadOnlyObjectStore
{
    /** @return iterable<string> Bounded byte chunks, never a caller-visible path. */
    public function chunks(string $relativePath): iterable;
}
