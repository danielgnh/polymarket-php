<?php

declare(strict_types=1);

namespace PolymarketPhp\Polymarket;

use PolymarketPhp\Polymarket\Auth\ClobAuthenticator;
use PolymarketPhp\Polymarket\Http\AsyncClientInterface;
use PolymarketPhp\Polymarket\Http\GuzzleHttpClient;
use PolymarketPhp\Polymarket\Http\HttpClientInterface;
use PolymarketPhp\Polymarket\Resources\Clob\Account;
use PolymarketPhp\Polymarket\Resources\Clob\Authentication;
use PolymarketPhp\Polymarket\Resources\Clob\Book;
use PolymarketPhp\Polymarket\Resources\Clob\Markets;
use PolymarketPhp\Polymarket\Resources\Clob\Orders;
use PolymarketPhp\Polymarket\Resources\Clob\OrderScoring;
use PolymarketPhp\Polymarket\Resources\Clob\Pricing;
use PolymarketPhp\Polymarket\Resources\Clob\Rewards;
use PolymarketPhp\Polymarket\Resources\Clob\Server;
use PolymarketPhp\Polymarket\Resources\Clob\Spreads;
use PolymarketPhp\Polymarket\Resources\Clob\Trades;

/**
 * CLOB API Client.
 *
 * Handles all CLOB (Central Limit Order Book) API operations.
 * https://clob.polymarket.com
 *
 * Resources:
 * - Book: Order book data and tick sizes
 * - Orders: Order management and order history
 * - Pricing: Price data and midpoints
 * - Spreads: Bid-ask spreads
 * - Trades: Trade history and execution
 * - Markets: Market data and listings
 * - Authentication: API key management
 * - Account: Balance, allowances, and notifications
 * - Rewards: Earnings and reward percentages
 * - OrderScoring: Order scoring checks
 * - Server: Health checks and server info
 *
 * Authentication:
 * - Read operations: Optional (for rate limiting)
 * - Write operations: Required (EIP712 signatures)
 */
class Clob
{
    private readonly HttpClientInterface $httpClient;

    public function __construct(
        private readonly Config $config,
        ?HttpClientInterface $httpClient = null,
        private ?ClobAuthenticator $authenticator = null,
        private readonly ?AsyncClientInterface $asyncClient = null,
    ) {
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

    public function book(): Book
    {
        return new Book($this->httpClient, $this->asyncClient);
    }

    public function orders(): Orders
    {
        return new Orders($this->authenticator?->getSigner(), $this->httpClient, $this->asyncClient);
    }

    public function pricing(): Pricing
    {
        return new Pricing($this->httpClient, $this->asyncClient);
    }

    public function spreads(): Spreads
    {
        return new Spreads($this->httpClient, $this->asyncClient);
    }

    public function trades(): Trades
    {
        return new Trades($this->httpClient, $this->asyncClient);
    }

    public function markets(): Markets
    {
        return new Markets($this->httpClient, $this->asyncClient);
    }

    public function authentication(): Authentication
    {
        return new Authentication($this->httpClient, $this->asyncClient);
    }

    public function account(): Account
    {
        return new Account($this->httpClient, $this->asyncClient);
    }

    public function rewards(): Rewards
    {
        return new Rewards($this->httpClient, $this->asyncClient);
    }

    public function orderScoring(): OrderScoring
    {
        return new OrderScoring($this->httpClient, $this->asyncClient);
    }

    public function server(): Server
    {
        return new Server($this->httpClient, $this->asyncClient);
    }
}
