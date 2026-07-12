<?php

declare(strict_types=1);

namespace PolymarketPhp\Polymarket\Enums;

/**
 * Order side — whether buying or selling shares.
 *
 * `forSignature()` returns the integer representation required by the
 * EIP-712 Order struct. For the CLOB API string representation use `->value`.
 */
enum OrderSide: string
{
    case BUY = 'BUY';
    case SELL = 'SELL';

    /**
     * Integer representation used in the EIP-712 Order struct.
     */
    public function forSignature(): int
    {
        return match ($this) {
            self::BUY  => 0,
            self::SELL => 1,
        };
    }
}
