<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Resources\Clob;

use Danielgnh\PolymarketPhp\Exceptions\PolymarketException;
use Danielgnh\PolymarketPhp\Resources\Resource;

class Orders extends Resource
{
    /**
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function get(string $token_id): array
    {
        // todo: this one is for the orderbook
        return $this->httpClient->get('/book', ['token_id' => $token_id])->json();
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
        $response = $this->httpClient->delete("/orders/$orderId");

        return $response->json();
    }
}
