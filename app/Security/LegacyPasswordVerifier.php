<?php

declare(strict_types=1);

namespace App\Security;

/**
 * Compatibility with the desktop PasswordHelper, not an authentication service.
 * Callers must independently enforce account state, source identity and city access.
 */
final class LegacyPasswordVerifier
{
    public function verify(string $password, string $storedHash): bool
    {
        // .NET strings are encoded as UTF-8 by Rfc2898DeriveBytes. Do not normalize
        // or trim passwords: either would change the historical credential.
        if (preg_match('//u', $password) !== 1) {
            return false;
        }

        // Convert.FromBase64String accepts these four whitespace characters.
        // A 48-byte payload has exactly 64 Base64 characters and no padding.
        $encoded = str_replace([' ', "\t", "\r", "\n"], '', $storedHash);

        if (preg_match('/\A[A-Za-z0-9+\/]{64}\z/', $encoded) !== 1) {
            return false;
        }

        $payload = base64_decode($encoded, true);

        if ($payload === false || strlen($payload) !== 48) {
            return false;
        }

        $salt = substr($payload, 0, 16);
        $expected = substr($payload, 16, 32);
        $actual = hash_pbkdf2('sha256', $password, $salt, 100_000, 32, true);

        return hash_equals($expected, $actual);
    }
}
