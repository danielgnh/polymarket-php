<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Resources\Gamma;

use Danielgnh\PolymarketPhp\Exceptions\PolymarketException;
use Danielgnh\PolymarketPhp\Resources\Resource;

class Search extends Resource
{
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
