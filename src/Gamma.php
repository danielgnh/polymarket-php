<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp;

use Danielgnh\PolymarketPhp\Exceptions\PolymarketException;
use Danielgnh\PolymarketPhp\Http\GuzzleHttpClient;
use Danielgnh\PolymarketPhp\Http\HttpClientInterface;
use Danielgnh\PolymarketPhp\Resources\Gamma\Comments;
use Danielgnh\PolymarketPhp\Resources\Gamma\Events;
use Danielgnh\PolymarketPhp\Resources\Gamma\Health;
use Danielgnh\PolymarketPhp\Resources\Gamma\Markets;
use Danielgnh\PolymarketPhp\Resources\Gamma\Search;
use Danielgnh\PolymarketPhp\Resources\Gamma\Series;
use Danielgnh\PolymarketPhp\Resources\Gamma\Sports;
use Danielgnh\PolymarketPhp\Resources\Gamma\Tags;

/**
 * Gamma API Client.
 *
 * Handles all Gamma API operations (read-only market data).
 * https://gamma-api.polymarket.com
 *
 * Resources:
 * - Health: API health check
 * - Sports: Sports metadata and teams
 * - Tags: Tag management and relationships
 * - Events: Event information and metadata
 * - Markets: Market information and metadata
 * - Series: Series information
 * - Comments: Market and event comments
 * - Search: Global search across markets, events, and profiles
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

    public function health(): Health
    {
        return new Health($this->httpClient);
    }

    public function sports(): Sports
    {
        return new Sports($this->httpClient);
    }

    public function tags(): Tags
    {
        return new Tags($this->httpClient);
    }

    public function events(): Events
    {
        return new Events($this->httpClient);
    }

    public function markets(): Markets
    {
        return new Markets($this->httpClient);
    }

    public function series(): Series
    {
        return new Series($this->httpClient);
    }

    public function comments(): Comments
    {
        return new Comments($this->httpClient);
    }

    /**
     * Search markets, events, and profiles.
     *
     * @param array<string, mixed> $filters
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function search(string $query, array $filters = []): array
    {
        $response = $this->httpClient->get('/public-search', [
            'q' => $query,
            ...$filters,
        ]);

        return $response->json();
    }
}
