<?php

declare(strict_types=1);

namespace App\Services\Attachments\Streaming;

final class LocalReadOnlyStore implements ReadOnlyObjectStore
{
    public function __construct(private readonly string $root, private readonly int $chunkBytes = 65536) {}

    public function chunks(string $relativePath): iterable
    {
        $relativePath = (new LegacyPathResolver)->resolve($relativePath);
        $root = realpath($this->root);
        if ($root === false || ! is_dir($root) || $this->chunkBytes < 1 || $this->chunkBytes > 65536) {
            throw new AttachmentReadFailure('unreadable');
        }
        $candidate = $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        clearstatcache(true);
        $resolved = realpath($candidate);
        if ($resolved === false) {
            throw new AttachmentReadFailure('missing');
        }
        $prefix = rtrim(str_replace('\\', '/', $root), '/').'/';
        $normalized = str_replace('\\', '/', $resolved);
        if (PHP_OS_FAMILY === 'Windows') {
            $prefix = strtolower($prefix);
            $normalized = strtolower($normalized);
        }
        if (! str_starts_with($normalized, $prefix) || ! is_file($resolved)) {
            throw new AttachmentReadFailure('unsafe_path');
        }
        $before = @stat($resolved);
        $handle = @fopen($resolved, 'rb');
        if ($handle === false) {
            throw new AttachmentReadFailure('unreadable');
        }
        try {
            $opened = fstat($handle);
            clearstatcache(true);
            if (! $this->sameFile($before, $opened) || realpath($candidate) !== $resolved) {
                throw new AttachmentReadFailure('changed_during_read');
            }
            while (! feof($handle)) {
                $bytes = fread($handle, $this->chunkBytes);
                if ($bytes === false || ($bytes === '' && ! feof($handle))) {
                    throw new AttachmentReadFailure('unreadable');
                }
                if ($bytes !== '') {
                    yield $bytes;
                }
            }
            clearstatcache(true);
            $after = @stat($candidate);
            if (realpath($candidate) !== $resolved || ! $this->sameFile($before, $after)
                || ! $this->sameFile($before, fstat($handle))) {
                throw new AttachmentReadFailure('changed_during_read');
            }
        } finally {
            fclose($handle);
        }
    }

    private function sameFile(array|false $a, array|false $b): bool
    {
        if ($a === false || $b === false || ! isset($a['ino'], $b['ino']) || $a['ino'] === 0 || $b['ino'] === 0) {
            return false;
        }
        foreach (['dev', 'ino', 'size', 'mtime', 'ctime'] as $key) {
            if ($a[$key] !== $b[$key]) {
                return false;
            }
        }

        return ($b['mode'] & 0170000) === 0100000;
    }
}
