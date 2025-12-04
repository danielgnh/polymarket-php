<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp;

use Danielgnh\PolymarketPhp\Auth\ClobAuthenticator;
use Danielgnh\PolymarketPhp\Http\GuzzleHttpClient;
use Danielgnh\PolymarketPhp\Http\HttpClientInterface;
use Danielgnh\PolymarketPhp\Resources\Clob\Account;
use Danielgnh\PolymarketPhp\Resources\Clob\Authentication;
use Danielgnh\PolymarketPhp\Resources\Clob\Book;
use Danielgnh\PolymarketPhp\Resources\Clob\Markets;
use Danielgnh\PolymarketPhp\Resources\Clob\Orders;
use Danielgnh\PolymarketPhp\Resources\Clob\OrderScoring;
use Danielgnh\PolymarketPhp\Resources\Clob\Pricing;
use Danielgnh\PolymarketPhp\Resources\Clob\Rewards;
use Danielgnh\PolymarketPhp\Resources\Clob\Server;
use Danielgnh\PolymarketPhp\Resources\Clob\Spreads;
use Danielgnh\PolymarketPhp\Resources\Clob\Trades;

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

    public function book(): Book
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

    public function trades(): Trades
    {
        return new Trades($this->httpClient);
    }

    public function markets(): Markets
    {
        return new Markets($this->httpClient);
    }

    public function authentication(): Authentication
    {
        return new Authentication($this->httpClient);
    }

    public function account(): Account
    {
        return new Account($this->httpClient);
    }

    public function rewards(): Rewards
    {
        return new Rewards($this->httpClient);
    }

    public function orderScoring(): OrderScoring
    {
        return new OrderScoring($this->httpClient);
    }

    public function server(): Server
    {
        return new Server($this->httpClient);
    }
}
