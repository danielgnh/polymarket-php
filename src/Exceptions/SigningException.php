<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Exceptions;

/**
 * Thrown when cryptographic signing fails.
 */
class SigningException extends PolymarketException
{
    public static function eip712Failed(string $reason): self
    {
        return new self("EIP-712 signing failed: $reason");
    }

    public static function hmacFailed(string $reason): self
    {
        return new self("HMAC signature generation failed: $reason");
    }
}
