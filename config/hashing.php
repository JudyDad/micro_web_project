<?php

return [
    // Avoid bcrypt's 72-byte truncation when upgrading legacy Unicode passwords.
    'driver' => 'argon2id',
    'argon' => ['memory' => 65536, 'threads' => 1, 'time' => 3, 'verify' => true],
    'rehash_on_login' => true,
];
