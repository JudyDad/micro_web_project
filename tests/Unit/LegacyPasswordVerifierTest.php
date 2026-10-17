<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Security\LegacyPasswordVerifier;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LegacyPasswordVerifierTest extends TestCase
{
    // Synthetic vectors generated independently with .NET, never production data:
    // salt = [byte[]](0..15)
    // Rfc2898DeriveBytes(password, salt, 100000, HashAlgorithmName.SHA256)
    // Convert.ToBase64String(salt + pbkdf.GetBytes(32))
    private const ASCII_HASH = 'AAECAwQFBgcICQoLDA0OD6rirHkgJUrCARvhwiO9AlEz7NUBCDqkiw29w+5zjOOk';

    #[DataProvider('desktopVectors')]
    public function test_it_verifies_independent_desktop_vectors(string $password, string $hash): void
    {
        self::assertTrue((new LegacyPasswordVerifier)->verify($password, $hash));
        self::assertFalse((new LegacyPasswordVerifier)->verify($password.'x', $hash));
    }

    public static function desktopVectors(): array
    {
        return [
            'ASCII' => ['Synthetic-Desktop-2026!', self::ASCII_HASH],
            'Arabic and supplementary Unicode' => ['كلمة مرور تجريبية 🔐', 'AAECAwQFBgcICQoLDA0OD2cR1qZ6lFs+AIY0IIZ1Te009An/Vd8Ct41jH6Mm53d3'],
            'empty desktop password' => ['', 'AAECAwQFBgcICQoLDA0ODyhu0ODsR8yVPccJ2oawdISeHBICzKSs1mPfGGBRSTSu'],
        ];
    }

    public function test_it_accepts_dotnet_base64_whitespace(): void
    {
        $hash = " \t\r\n".chunk_split(self::ASCII_HASH, 8, " \t\r\n");

        self::assertTrue((new LegacyPasswordVerifier)->verify('Synthetic-Desktop-2026!', $hash));
    }

    #[DataProvider('malformedHashes')]
    public function test_it_denies_malformed_hashes(string $hash): void
    {
        self::assertFalse((new LegacyPasswordVerifier)->verify('Synthetic-Desktop-2026!', $hash));
    }

    public static function malformedHashes(): array
    {
        return [
            'empty' => [''],
            'whitespace only' => [" \t\r\n"],
            'invalid alphabet' => ['!'.substr(self::ASCII_HASH, 1)],
            'extra padding' => [self::ASCII_HASH.'='],
            'short payload' => [base64_encode(str_repeat('a', 47))],
            'long payload' => [base64_encode(str_repeat('a', 49))],
            'short encoding' => [substr(self::ASCII_HASH, 1)],
            'null byte' => [self::ASCII_HASH."\0"],
            'nonbreaking space' => [self::ASCII_HASH."\u{00A0}"],
            'vertical tab' => [self::ASCII_HASH."\v"],
        ];
    }

    public function test_it_does_not_trim_passwords_or_accept_empty_in_place_of_nonempty(): void
    {
        $verifier = new LegacyPasswordVerifier;

        self::assertFalse($verifier->verify('', self::ASCII_HASH));
        self::assertFalse($verifier->verify(' Synthetic-Desktop-2026!', self::ASCII_HASH));
        self::assertFalse($verifier->verify('Synthetic-Desktop-2026! ', self::ASCII_HASH));
        self::assertFalse($verifier->verify("\xFF", self::ASCII_HASH));
    }

    public function test_it_denies_a_changed_salt_or_derived_key(): void
    {
        $verifier = new LegacyPasswordVerifier;
        $payload = base64_decode(self::ASCII_HASH, true);

        foreach ([0, 16, 47] as $offset) {
            $tampered = $payload;
            $tampered[$offset] = chr(ord($tampered[$offset]) ^ 1);

            self::assertFalse($verifier->verify('Synthetic-Desktop-2026!', base64_encode($tampered)));
        }
    }
}
