<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp;

use Danielgnh\PolymarketPhp\Http\GuzzleHttpClient;
use Danielgnh\PolymarketPhp\Http\HttpClientInterface;
use Danielgnh\PolymarketPhp\Resources\Markets;

/**
 * Gamma API Client.
 *
 * Handles all Gamma API operations (read-only market data).
 * https://gamma-api.polymarket.com
 *
 * Resources:
 * - Markets: Market information and metadata
 * - Events: Event information (future)
 */
class Gamma
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
        $this->httpClient = $httpClient ?? new GuzzleHttpClient($this->config->gammaBaseUrl, $this->config);
    }

    public function markets(): Markets
    {
        return new Markets($this->httpClient);
    }
}
