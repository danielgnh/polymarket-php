<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Enums;

/**
 * Order type determines execution behavior.
 */
enum OrderType: string
{
    case FOK = 'FOK';
    case FAK = 'FAK';
    case GTC = 'GTC';
    case GTD = 'GTD';
}
