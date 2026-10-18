<?php

declare(strict_types=1);

namespace App\Services\Attachments\Streaming;

use App\Services\AccountAuditTrail;

/** Explicit FTPS, certificate validation and a separately reviewed server jail. */
final class CurlFtpReadTransport implements FtpReadTransport
{
    public function __construct(private readonly array $configuration) {}

    public function retrieve(string $relativePath, mixed $target, int $maxBytes): void
    {
        $path = (new LegacyPathResolver)->resolve($relativePath);
        $config = $this->configuration;
        $host = $config['host'] ?? '';
        $root = $config['root'] ?? null;
        $port = $config['port'] ?? 21;
        if (! extension_loaded('curl') || ! is_resource($target) || $maxBytes < 0
            || ($config['enabled'] ?? null) !== true || ($config['server_jail_verified'] ?? null) !== true
            || ! AccountAuditTrail::validDecision($config['confinement_decision'] ?? '')
            || ! is_string($host) || ! preg_match('/\A[a-zA-Z0-9][a-zA-Z0-9.\-]{0,252}\z/D', $host)
            || ! is_int($port) || $port < 1 || $port > 65535 || ! is_string($root) || ! str_starts_with($root, '/')
            || ! is_string($config['username'] ?? null) || ! is_string($config['password'] ?? null)) {
            throw new AttachmentReadFailure('unreadable');
        }
        $rootPath = $root === '/' ? '' : (new LegacyPathResolver)->resolve(substr($root, 1));
        $remote = ($rootPath === '' ? '' : $rootPath.'/').$path;
        $encoded = implode('/', array_map('rawurlencode', explode('/', $remote)));
        $handle = curl_init('ftp://'.$host.':'.$port.'/'.$encoded);
        if ($handle === false) {
            throw new AttachmentReadFailure('unreadable');
        }
        $total = 0;
        try {
            $options = [
                CURLOPT_PROTOCOLS => CURLPROTO_FTP,
                CURLOPT_REDIR_PROTOCOLS => 0,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_PROXY => '',
                CURLOPT_USERPWD => $config['username'].':'.$config['password'],
                CURLOPT_USE_SSL => CURLUSESSL_ALL,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
                CURLOPT_FTP_SKIP_PASV_IP => true,
                CURLOPT_FTP_USE_EPSV => true,
                CURLOPT_TRANSFERTEXT => false,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_LOW_SPEED_LIMIT => 1,
                CURLOPT_LOW_SPEED_TIME => 15,
                CURLOPT_WRITEFUNCTION => static function ($curl, string $bytes) use ($target, $maxBytes, &$total): int {
                    $length = strlen($bytes);
                    if ($total + $length > $maxBytes) {
                        return 0;
                    }
                    $written = fwrite($target, $bytes);
                    if ($written !== $length) {
                        return 0;
                    }
                    $total += $length;

                    return $length;
                },
            ];
            if (! @curl_setopt_array($handle, $options) || @curl_exec($handle) !== true) {
                throw new AttachmentReadFailure(curl_errno($handle) === CURLE_REMOTE_FILE_NOT_FOUND ? 'missing' : 'unreadable');
            }
        } finally {
            curl_close($handle);
        }
    }
}
