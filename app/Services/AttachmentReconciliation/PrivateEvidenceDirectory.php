<?php

declare(strict_types=1);

namespace App\Services\AttachmentReconciliation;

use RuntimeException;
use Symfony\Component\Process\Process;

final class PrivateEvidenceDirectory
{
    public static function create(string $requested): string
    {
        $parent = realpath(dirname($requested));
        $name = basename($requested);
        if ($parent === false || ! is_dir($parent) || in_array($name, ['', '.', '..'], true) || str_contains($name, "\0")) {
            throw new RuntimeException('Private evidence requires an existing parent and a new directory.');
        }
        $target = $parent.DIRECTORY_SEPARATOR.$name;
        $repository = realpath(base_path());
        $private = realpath(storage_path('app/private'));
        if ($repository === false || (self::within($target, $repository) && ($private === false || ! self::within($target, $private)))) {
            throw new RuntimeException('Evidence cannot be written to repository or public paths.');
        }
        if (file_exists($target) || is_link($target) || ! @mkdir($target, 0700)) {
            throw new RuntimeException('Evidence directory already exists or cannot be created.');
        }
        if (PHP_OS_FAMILY === 'Windows') {
            $identity = new Process(['whoami', '/user', '/fo', 'csv', '/nh']);
            $identity->run();
            if (! $identity->isSuccessful() || ! preg_match('/\b(S-1-\d+(?:-\d+)+)\b/', $identity->getOutput(), $match)) {
                throw new RuntimeException('Private evidence owner identity cannot be established.');
            }
            $permissions = new Process(['icacls', $target, '/inheritance:r', '/grant:r', '*'.$match[1].':(OI)(CI)F']);
            $permissions->run();
            if (! $permissions->isSuccessful()) {
                throw new RuntimeException('Private evidence access cannot be restricted.');
            }
        } elseif (! chmod($target, 0700)) {
            throw new RuntimeException('Private evidence directory permissions cannot be restricted.');
        }

        return $target;
    }

    public static function write(string $directory, string $filename, string $bytes): void
    {
        if ($filename !== basename($filename) || ! preg_match('/\A[a-zA-Z0-9_.-]+\z/D', $filename) || in_array($filename, ['.', '..'], true)) {
            throw new RuntimeException('Private evidence filenames must be local fixed names.');
        }
        $stream = @fopen($directory.DIRECTORY_SEPARATOR.$filename, 'xb');
        if ($stream === false) {
            throw new RuntimeException('Private evidence file already exists or cannot be created.');
        }
        try {
            if (PHP_OS_FAMILY !== 'Windows' && ! chmod($directory.DIRECTORY_SEPARATOR.$filename, 0600)) {
                throw new RuntimeException('Private evidence file permissions cannot be restricted.');
            }
            self::append($stream, $bytes);
            if (! fflush($stream) || (function_exists('fsync') && ! fsync($stream))) {
                throw new RuntimeException('Private evidence could not be durably flushed.');
            }
        } finally {
            fclose($stream);
        }
    }

    public static function append(mixed $stream, string $bytes): void
    {
        $offset = 0;
        while ($offset < strlen($bytes)) {
            $written = fwrite($stream, substr($bytes, $offset));
            if ($written === false || $written === 0) {
                throw new RuntimeException('Incomplete private evidence write.');
            }
            $offset += $written;
        }
    }

    private static function within(string $path, string $root): bool
    {
        $path = str_replace('\\', '/', $path);
        $root = rtrim(str_replace('\\', '/', $root), '/');
        if (PHP_OS_FAMILY === 'Windows') {
            $path = strtolower($path);
            $root = strtolower($root);
        }

        return $path === $root || str_starts_with($path, $root.'/');
    }
}
