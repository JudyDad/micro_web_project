<?php

declare(strict_types=1);

namespace App\Services\Attachments;

interface AttachmentCatalog
{
    public function find(string $opaqueReference): ?AttachmentReference;
}
