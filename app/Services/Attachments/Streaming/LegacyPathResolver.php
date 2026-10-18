<?php

declare(strict_types=1);

namespace App\Services\Attachments\Streaming;

/** Validates exact relative UTF-8 bytes; never decodes, trims or repairs a path. */
final class LegacyPathResolver
{
    public const VERSION = 'relative-utf8-v1';

    public function resolve(string $raw): string
    {
        if ($raw === '' || strlen($raw) > 4096 || preg_match('//u', $raw) !== 1
            || preg_match('~[\x00-\x1f\x7f\\\\:%?*<>|"\x23]~', $raw) || str_starts_with($raw, '/')) {
            throw new AttachmentReadFailure('unsafe_path');
        }
        foreach (explode('/', $raw) as $part) {
            if ($part === '' || $part === '.' || $part === '..' || preg_match('/[. ]$/D', $part)
                || preg_match('/^(?:CON|PRN|AUX|NUL|COM[1-9]|LPT[1-9])(?:\.|$)/i', $part)) {
                throw new AttachmentReadFailure('unsafe_path');
            }
        }

        return $raw;
    }
}
