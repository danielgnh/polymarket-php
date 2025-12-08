<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Resources\Gamma;

use Danielgnh\PolymarketPhp\Exceptions\PolymarketException;
use Danielgnh\PolymarketPhp\Resources\Resource;

class Markets extends Resource
{
    /**
     * @param array<string, mixed> $filters
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws PolymarketException
     */
    public function list(array $filters = [], int $limit = 100, int $offset = 0): array
    {
        $response = $this->httpClient->get('/markets', [
            'limit' => $limit,
            'offset' => $offset,
            ...$filters,
        ]);

        /** @var array<int, array<string, mixed>> */
        return $response->json();
    }

    /**
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function get(string $marketId): array
    {
        $response = $this->httpClient->get("/markets/$marketId");

        return $response->json();
    }

    /**
     * Get market by slug.
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function getBySlug(string $slug): array
    {
        $response = $this->httpClient->get("/markets/slug/$slug");

        return $response->json();
    }

    /**
     * Get all tags associated with a specific market.
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws PolymarketException
     */
    public function tags(string $marketId): array
    {
        $response = $this->httpClient->get("/markets/$marketId/tags");

        /** @var array<int, array<string, mixed>> */
        return $response->json();
    }
}
