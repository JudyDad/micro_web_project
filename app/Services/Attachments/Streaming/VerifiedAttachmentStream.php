<?php

declare(strict_types=1);

namespace App\Services\Attachments\Streaming;

use Throwable;

/** Private staged bytes are verified before HTTP can send its first byte. */
final class VerifiedAttachmentStream
{
    private function __construct(private mixed $handle, private readonly int $size, private readonly string $digest) {}

    public static function stage(iterable $chunks, string $sha256, int $length, int $maxBytes, int $timeoutSeconds = 60): self
    {
        if ($length < 0 || $maxBytes < 1 || $length > $maxBytes || $timeoutSeconds < 1 || $timeoutSeconds > 300
            || preg_match('/\A[0-9a-f]{64}\z/D', $sha256) !== 1) {
            throw new AttachmentReadFailure('unreadable');
        }
        $handle = tmpfile();
        if ($handle === false) {
            throw new AttachmentReadFailure('unreadable');
        }
        $total = 0;
        $hash = hash_init('sha256');
        $started = hrtime(true);
        try {
            foreach ($chunks as $bytes) {
                if (! is_string($bytes) || strlen($bytes) > 65536 || $total + strlen($bytes) > $length
                    || (hrtime(true) - $started) / 1e9 > $timeoutSeconds) {
                    throw new AttachmentReadFailure('changed_during_read');
                }
                $total += strlen($bytes);
                hash_update($hash, $bytes);
                if (fwrite($handle, $bytes) !== strlen($bytes)) {
                    throw new AttachmentReadFailure('unreadable');
                }
            }
            if ($total !== $length || ! hash_equals($sha256, hash_final($hash))) {
                throw new AttachmentReadFailure('changed_during_read');
            }
            rewind($handle);

            return new self($handle, $total, $sha256);
        } catch (Throwable $error) {
            fclose($handle);
            throw $error instanceof AttachmentReadFailure ? $error : new AttachmentReadFailure('unreadable');
        }
    }

    public function length(): int
    {
        return $this->size;
    }

    public function sha256(): string
    {
        return $this->digest;
    }

    public function chunks(): iterable
    {
        if (! is_resource($this->handle)) {
            throw new AttachmentReadFailure('unreadable');
        }
        try {
            while (! feof($this->handle)) {
                $bytes = fread($this->handle, 65536);
                if ($bytes === false || ($bytes === '' && ! feof($this->handle))) {
                    throw new AttachmentReadFailure('unreadable');
                }
                if ($bytes !== '') {
                    yield $bytes;
                }
            }
        } finally {
            $this->close();
        }
    }

    public function close(): void
    {
        if (is_resource($this->handle)) {
            fclose($this->handle);
        }
        $this->handle = null;
    }

    public function __destruct()
    {
        $this->close();
    }
}
