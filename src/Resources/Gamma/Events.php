<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Resources\Gamma;

use Danielgnh\PolymarketPhp\Exceptions\PolymarketException;
use Danielgnh\PolymarketPhp\Resources\Resource;

class Events extends Resource
{
    /**
     * List events with pagination.
     *
     * @param array<string, mixed> $filters
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws PolymarketException
     */
    public function list(array $filters = [], int $limit = 100, int $offset = 0): array
    {
        $response = $this->httpClient->get('/events', [
            'limit' => $limit,
            'offset' => $offset,
            ...$filters,
        ]);

        /** @var array<int, array<string, mixed>> */
        return $response->json();
    }

    /**
     * Get event by ID.
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function get(string $eventId): array
    {
        $response = $this->httpClient->get("/events/$eventId");

        return $response->json();
    }

    /**
     * Get event by slug.
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function getBySlug(string $slug): array
    {
        $response = $this->httpClient->get("/events/slug/$slug");

        return $response->json();
    }

    /**
     * Get all tags associated with a specific event.
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws PolymarketException
     */
    public function tags(string $eventId): array
    {
        $response = $this->httpClient->get("/events/$eventId/tags");

        /** @var array<int, array<string, mixed>> */
        return $response->json();
    }
}
