<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\AccountAuditTrail;
use App\Services\Attachments\AttachmentAccess;
use App\Services\Attachments\FrozenAttachmentCatalog;
use App\Services\Attachments\ResolvedAttachment;
use App\Services\Attachments\Streaming\AttachmentStreamFactory;
use App\Services\Attachments\Streaming\VerifiedAttachmentStream;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

final class AttachmentController
{
    public function download(Request $request, string $locale, string $reference, FrozenAttachmentCatalog $catalog,
        AttachmentAccess $access, AttachmentStreamFactory $streams, AccountAuditTrail $audit): Response
    {
        abort_if($request->headers->has('Range'), 416);
        $stream = null;
        $resolved = null;
        try {
            $entry = $catalog->find($reference);
            abort_if($entry === null, 404);
            $resolved = $access->authorize($request->user(), $entry);
            $stream = $streams->open($resolved);
            if ($stream->length() !== $entry->expectedLength || $stream->sha256() !== $entry->expectedSha256) {
                throw new \RuntimeException('Prepared attachment does not match its reviewed byte evidence.');
            }
            // Staging can take time; revocation during preparation must still
            // deny before success headers or a HEAD metadata response.
            $resolved = $access->authorize($request->user(), $entry);
            $this->event($audit, $request, $resolved, 'started', 'succeeded');
            $headers = ['Content-Type' => 'application/octet-stream', 'Content-Length' => (string) $stream->length(),
                'Cache-Control' => 'private, no-store, max-age=0', 'Pragma' => 'no-cache', 'Expires' => '0',
                'X-Content-Type-Options' => 'nosniff', 'Accept-Ranges' => 'none'];
            $name = 'attachment-'.$entry->id.'.bin';
            if ($request->isMethod('HEAD')) {
                $stream->close();

                return response('', 200, $headers + ['Content-Disposition' => 'attachment; filename="'.$name.'"']);
            }

            return response()->streamDownload(function () use ($request, $entry, $resolved, $stream, $access, $audit): void {
                $bytes = 0;
                $digest = hash_init('sha256');
                try {
                    // A pre-authorized response or copied URL does not preserve
                    // a revoked grant/publication while waiting to start sending.
                    $access->authorize($request->user(), $entry);
                    foreach ($stream->chunks() as $chunk) {
                        $access->authorize($request->user(), $entry);
                        if (connection_aborted() || ! is_string($chunk) || strlen($chunk) > (int) config('attachments.chunk_bytes', 65536)
                            || $bytes + strlen($chunk) > $entry->expectedLength) {
                            throw new \RuntimeException('Attachment stream stopped before completion.');
                        }
                        $bytes += strlen($chunk);
                        hash_update($digest, $chunk);
                        echo $chunk;
                    }
                    if ($bytes !== $entry->expectedLength || hash_final($digest) !== $entry->expectedSha256) {
                        throw new \RuntimeException('Attachment streamed evidence mismatch.');
                    }
                    $this->event($audit, $request, $resolved, 'succeeded', 'succeeded');
                } catch (Throwable) {
                    // Headers may already have been sent. Close without more
                    // bytes or diagnostic text; partial delivery is not success.
                    $this->safeFailure($audit, $request, $resolved);
                } finally {
                    $this->close($stream);
                }
            }, $name, $headers, 'attachment');
        } catch (HttpExceptionInterface $error) {
            $this->close($stream);
            throw $error;
        } catch (Throwable) {
            $this->close($stream);
            if ($resolved instanceof ResolvedAttachment) {
                $this->safeFailure($audit, $request, $resolved);
            }
            abort(503, 'Attachment is temporarily unavailable.');
        }
    }

    private function event(AccountAuditTrail $audit, Request $request, ResolvedAttachment $attachment, string $stage, string $outcome): void
    {
        $audit->record($request->user()->id, 'attachments.download.'.$stage, $outcome,
            city: $attachment->cityId, subjectType: 'attachment_record', subjectId: $attachment->recordId);
    }

    private function safeFailure(AccountAuditTrail $audit, Request $request, ResolvedAttachment $attachment): void
    {
        try {
            $this->event($audit, $request, $attachment, 'failed', 'failed');
        } catch (Throwable) {
            // Never render infrastructure/private metadata inside download bytes.
        }
    }

    private function close(?VerifiedAttachmentStream $stream): void
    {
        try {
            $stream?->close();
        } catch (Throwable) {
            // A cleanup failure must never append a private filesystem diagnostic.
        }
    }
}
