<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Attachments\Streaming\AttachmentReadFailure;
use App\Services\Attachments\Streaming\FtpReadOnlyStore;
use App\Services\Attachments\Streaming\FtpReadTransport;
use App\Services\Attachments\Streaming\LegacyPathResolver;
use App\Services\Attachments\Streaming\LocalReadOnlyStore;
use App\Services\Attachments\Streaming\VerifiedAttachmentStream;
use PHPUnit\Framework\TestCase;

final class AttachmentStreamingTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir().DIRECTORY_SEPARATOR.'phase3-attachment-'.bin2hex(random_bytes(8));
        self::assertTrue(mkdir($this->root));
    }

    protected function tearDown(): void
    {
        $link = $this->root.DIRECTORY_SEPARATOR.'escape';
        $nestedFile = $this->root.DIRECTORY_SEPARATOR.'nested'.DIRECTORY_SEPARATOR.'payload.bin';
        $nested = dirname($nestedFile);
        $outside = $this->root.'.outside';

        if (is_link($link) || is_file($link)) {
            unlink($link);
        }
        if (is_file($nestedFile)) {
            unlink($nestedFile);
        }
        if (is_dir($nested)) {
            rmdir($nested);
        }
        if (is_file($outside)) {
            unlink($outside);
        }
        if (is_dir($this->root)) {
            rmdir($this->root);
        }
    }

    public function test_relative_utf8_paths_are_preserved_but_unsafe_paths_are_rejected(): void
    {
        $resolver = new LegacyPathResolver;
        $safe = 'documents/دليل-المشروع.pdf';
        self::assertSame($safe, $resolver->resolve($safe));

        foreach (['../secret', 'folder/../secret', '/absolute', 'C:\\secret', 'folder\\secret', 'a//b', 'CON.txt', 'name%2fsecret', "bad\0name", "\xFF"] as $unsafe) {
            try {
                $resolver->resolve($unsafe);
                self::fail('Unsafe legacy path was accepted.');
            } catch (AttachmentReadFailure $failure) {
                self::assertSame('unsafe_path', $failure->state);
            }
        }
    }

    public function test_local_store_reads_the_exact_object_from_its_root(): void
    {
        $directory = $this->root.DIRECTORY_SEPARATOR.'nested';
        self::assertTrue(mkdir($directory));
        $payload = "\x00\x01Arabic-عينة\xFF";
        self::assertSame(strlen($payload), file_put_contents($directory.DIRECTORY_SEPARATOR.'payload.bin', $payload));

        $store = new LocalReadOnlyStore($this->root, 3);

        self::assertSame($payload, implode('', iterator_to_array($store->chunks('nested/payload.bin'))));
    }

    public function test_local_store_rejects_a_symlink_that_escapes_its_root(): void
    {
        $outside = $this->root.'.outside';
        self::assertSame(strlen('outside-root'), file_put_contents($outside, 'outside-root'));
        if (! @symlink($outside, $this->root.DIRECTORY_SEPARATOR.'escape')) {
            self::markTestSkipped('This host does not permit creating a symlink for the confinement check.');
        }

        try {
            iterator_to_array((new LocalReadOnlyStore($this->root))->chunks('escape'));
            self::fail('The local store followed a symlink outside its root.');
        } catch (AttachmentReadFailure $failure) {
            self::assertSame('unsafe_path', $failure->state);
        }
    }

    public function test_verified_stream_stages_exact_bytes_and_rejects_length_or_digest_changes(): void
    {
        $payload = "\x00project-file\xFF";
        $stream = VerifiedAttachmentStream::stage([$payload], hash('sha256', $payload), strlen($payload), 1024);
        self::assertSame($payload, implode('', iterator_to_array($stream->chunks())));
        $stream->close();

        foreach ([
            [[$payload], hash('sha256', $payload), strlen($payload) + 1, 1024, 'changed_during_read'],
            [[$payload], str_repeat('0', 64), strlen($payload), 1024, 'changed_during_read'],
            [[$payload], hash('sha256', $payload), strlen($payload), strlen($payload) - 1, 'unreadable'],
        ] as [$chunks, $digest, $length, $maximum, $state]) {
            try {
                VerifiedAttachmentStream::stage($chunks, $digest, $length, $maximum);
                self::fail('Changed or oversized attachment bytes were accepted.');
            } catch (AttachmentReadFailure $failure) {
                self::assertSame($state, $failure->state);
            }
        }
    }

    public function test_ftps_store_passes_only_a_validated_relative_path_to_its_read_transport(): void
    {
        $payload = 'remote-object';
        $transport = new class($payload) implements FtpReadTransport
        {
            public ?string $path = null;

            public function __construct(private readonly string $payload) {}

            public function retrieve(string $relativePath, mixed $target, int $maxBytes): void
            {
                $this->path = $relativePath;
                if (strlen($this->payload) > $maxBytes || fwrite($target, $this->payload) !== strlen($this->payload)) {
                    throw new AttachmentReadFailure('changed_during_read');
                }
            }
        };
        $store = new FtpReadOnlyStore($transport, 1024);

        self::assertSame($payload, implode('', iterator_to_array($store->chunks('folder/object.bin'))));
        self::assertSame('folder/object.bin', $transport->path);

        try {
            iterator_to_array($store->chunks('../outside.bin'));
            self::fail('The FTPS store passed an unsafe path to the transport.');
        } catch (AttachmentReadFailure $failure) {
            self::assertSame('unsafe_path', $failure->state);
        }
    }
}
