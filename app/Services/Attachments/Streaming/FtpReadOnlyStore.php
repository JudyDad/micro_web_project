<?php

declare(strict_types=1);

namespace App\Services\Attachments\Streaming;

final class FtpReadOnlyStore implements ReadOnlyObjectStore
{
    public function __construct(private readonly FtpReadTransport $transport, private readonly int $maxBytes) {}

    public function chunks(string $relativePath): iterable
    {
        $path = (new LegacyPathResolver)->resolve($relativePath);
        $handle = tmpfile();
        if ($handle === false) {
            throw new AttachmentReadFailure('unreadable');
        }
        try {
            $this->transport->retrieve($path, $handle, $this->maxBytes);
            $size = ftell($handle);
            if ($size === false || $size > $this->maxBytes) {
                throw new AttachmentReadFailure('changed_during_read');
            }
            rewind($handle);
            while (! feof($handle)) {
                $bytes = fread($handle, 65536);
                if ($bytes === false || ($bytes === '' && ! feof($handle))) {
                    throw new AttachmentReadFailure('unreadable');
                }
                if ($bytes !== '') {
                    yield $bytes;
                }
            }
        } finally {
            fclose($handle);
        }
    }
}
