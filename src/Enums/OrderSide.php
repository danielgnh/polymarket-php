<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Enums;

/**
 * Order side - whether buying or selling shares.
 */
enum OrderSide: string
{
    case BUY = 'BUY';
    case SELL = 'SELL';
}
