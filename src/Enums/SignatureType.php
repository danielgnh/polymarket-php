<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Enums;

/**
 * Signature type for order authentication.
 */
enum SignatureType: int
{
    case POLYMARKET_PROXY_EMAIL = 1;
    case POLYMARKET_PROXY_WALLET = 2;
    case EOA = 0;
}
