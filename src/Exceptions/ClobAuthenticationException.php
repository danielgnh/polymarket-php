<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Exceptions;

/**
 * Thrown when CLOB authentication fails.
 */
class ClobAuthenticationException extends AuthenticationException
{
    public static function notSetup(): self
    {
        return new self('CLOB write operations require authentication.');
    }

    public static function invalidPrivateKey(string $reason): self
    {
        return new self("Invalid private key: $reason");
    }

    public static function credentialDerivationFailed(string $message): self
    {
        return new self("Failed to derive API credentials: $message");
    }

    public static function missingPrivateKey(): self
    {
        return new self('Private key is required for CLOB authentication.');
    }
}
