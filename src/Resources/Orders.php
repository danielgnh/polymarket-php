<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Resources;

use Danielgnh\PolymarketPhp\Exceptions\PolymarketException;

class Orders extends Resource
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
        $response = $this->httpClient->get('/orders', [
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
    public function get(string $orderId): array
    {
        $response = $this->httpClient->get("/orders/{$orderId}");

        return $response->json();
    }

    /**
     * @param array<string, mixed> $orderData
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function create(array $orderData): array
    {
        $response = $this->httpClient->post('/orders', $orderData);

        return $response->json();
    }

    /**
     * @return array<string, mixed>
     * @throws PolymarketException
     */
    public function cancel(string $orderId): array
    {
        $response = $this->httpClient->delete("/orders/{$orderId}");

        return $response->json();
    }
}
