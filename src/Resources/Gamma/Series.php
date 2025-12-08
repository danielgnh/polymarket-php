<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Resources\Gamma;

use Danielgnh\PolymarketPhp\Exceptions\PolymarketException;
use Danielgnh\PolymarketPhp\Resources\Resource;

class Series extends Resource
{
    /**
     * List series.
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws PolymarketException
     */
    public function list(): array
    {
        $response = $this->httpClient->get('/series');

        /** @var array<int, array<string, mixed>> */
        return $response->json();
    }

    /**
     * Get series by ID.
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function get(string $seriesId): array
    {
        $response = $this->httpClient->get("/series/$seriesId");

        return $response->json();
    }
}
