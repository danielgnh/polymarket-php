<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Enums;

enum OrderStatus: string
{
    case MATCHED = 'matched';
    case LIVE = 'live';
    case DELAYED = 'delayed';
    case UNMATCHED = 'unmatched';
}
