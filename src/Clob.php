<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp;

use Danielgnh\PolymarketPhp\Auth\ClobAuthenticator;
use Danielgnh\PolymarketPhp\Http\GuzzleHttpClient;
use Danielgnh\PolymarketPhp\Http\HttpClientInterface;
use Danielgnh\PolymarketPhp\Resources\Clob\Book;
use Danielgnh\PolymarketPhp\Resources\Clob\Orders;
use Danielgnh\PolymarketPhp\Resources\Clob\Pricing;
use Danielgnh\PolymarketPhp\Resources\Clob\Spreads;

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

    private ?ClobAuthenticator $authenticator;

    /**
     * @param Config                   $config
     * @param HttpClientInterface|null $httpClient
     * @param ClobAuthenticator|null   $authenticator
     */
    public function __construct(
        private readonly Config $config,
        ?HttpClientInterface $httpClient = null,
        ?ClobAuthenticator $authenticator = null
    ) {
        $this->authenticator = $authenticator;
        $this->httpClient = $httpClient ?? new GuzzleHttpClient(
            $this->config->clobBaseUrl,
            $this->config,
            $this->authenticator
        );
    }

    public function auth(ClobAuthenticator $authenticator): void
    {
        $this->authenticator = $authenticator;

        if ($this->httpClient instanceof GuzzleHttpClient) {
            $this->httpClient->auth($authenticator);
        }
    }

	public function orderbook(): Book
	{
		return new Book($this->httpClient);
	}

    public function orders(): Orders
    {
        return new Orders($this->httpClient);
    }

	public function pricing(): Pricing
	{
		return new Pricing($this->httpClient);
	}

	public function spreads(): Spreads
	{
		return new Spreads($this->httpClient);
	}
}
