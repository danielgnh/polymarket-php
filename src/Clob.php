<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp;

use Danielgnh\PolymarketPhp\Http\GuzzleHttpClient;
use Danielgnh\PolymarketPhp\Http\HttpClientInterface;
use Danielgnh\PolymarketPhp\Resources\Orders;

/**
 * CLOB API Client.
 *
 * Handles all CLOB (Central Limit Order Book) API operations.
 * https://clob.polymarket.com
 *
 * Resources:
 * - Orders: Order management and order history
 * - OrderBook: Order book data (future)
 * - Trades: Trade history and execution (future)
 *
 * Authentication:
 * - Read operations: Optional (for rate limiting)
 * - Write operations: Required (EIP712 signatures)
 */
class Clob
{
    private HttpClientInterface $httpClient;

    /**
     * @param Config                   $config
     * @param HttpClientInterface|null $httpClient
     */
    public function __construct(
        private readonly Config $config,
        ?HttpClientInterface $httpClient = null
    ) {
        $this->httpClient = $httpClient ?? new GuzzleHttpClient($this->config->clobBaseUrl, $this->config);
    }

    public function orders(): Orders
    {
        return new Orders($this->httpClient);
    }
}
