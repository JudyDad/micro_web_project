<?php

declare(strict_types=1);

namespace App\Services\Attachments\Streaming;

use RuntimeException;

final class AttachmentReadFailure extends RuntimeException
{
    public function __construct(public readonly string $state)
    {
        parent::__construct('Attachment read unavailable.');
    }
}
