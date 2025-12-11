<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Laravel\Facades;

use Danielgnh\PolymarketPhp\Client;
use Danielgnh\PolymarketPhp\Clob;
use Danielgnh\PolymarketPhp\Gamma;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Gamma gamma()
 * @method static Clob clob()
 * @method static void auth(?string $privateKey = null, int $nonce = 0)
 *
 * @see Client
 */
class Polymarket extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Client::class;
    }
}
