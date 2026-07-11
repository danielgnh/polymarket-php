<?php

declare(strict_types=1);

namespace PolymarketPhp\Polymarket\Enums;

/**
 * Signature type for order authentication (CLOB V2 canonical values).
 */
enum SignatureType: int
{
    /** ECDSA EIP-712 signature signed by an EOA. */
    case EOA = 0;

    /** EIP-712 signature signed by the EOA owning a Polymarket proxy wallet. */
    case POLY_PROXY = 1;

    /** EIP-712 signature signed by the EOA owning a Polymarket Gnosis Safe. */
    case POLY_GNOSIS_SAFE = 2;

    /** EIP-1271 signature from a smart contract wallet (not supported for signing yet). */
    case POLY_1271 = 3;
}
