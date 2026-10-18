<?php

return [
    // Activation needs an independently verified private catalog and an explicit
    // reviewed owner policy; importing rows or granting projects.view does not enable it.
    'enabled' => false,
    'catalog_path' => null,
    'catalog_sha256' => null,
    'decision_locator' => null,
    'max_bytes' => 128 * 1024 * 1024,
    'chunk_bytes' => 64 * 1024,
    'stores' => [],
];
