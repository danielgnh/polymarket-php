<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Resources\Clob;

use Danielgnh\PolymarketPhp\Exceptions\PolymarketException;
use Danielgnh\PolymarketPhp\Resources\Resource;

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
        $params = array_merge($filters, [
            'limit' => $limit,
            'offset' => $offset,
        ]);

        return $this->httpClient->get('/orders', $params)->json();
    }

    /**
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function get(string $orderId): array
    {
        return $this->httpClient->get("/orders/{$orderId}")->json();
    }

    /**
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function getOpen(array $params = []): array
    {
        return $this->httpClient->get('/open-orders', $params)->json();
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
        return $this->httpClient->post('/orders', $orderData)->json();
    }

    /**
     * @param array<string, mixed> $orderData
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function post(array $orderData): array
    {
        return $this->httpClient->post('/order', $orderData)->json();
    }

    /**
     * @param array<int, array<string, mixed>> $orders
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function postMultiple(array $orders): array
    {
        return $this->httpClient->post('/orders', $orders)->json();
    }

    /**
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function cancel(string|array $orderIdOrPayload): array
    {
        if (is_string($orderIdOrPayload)) {
            return $this->httpClient->delete("/orders/{$orderIdOrPayload}")->json();
        }

        return $this->httpClient->delete('/order', $orderIdOrPayload)->json();
    }

    /**
     * @param array<int, string> $orderIds
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function cancelMultiple(array $orderIds): array
    {
        return $this->httpClient->delete('/orders', ['ids' => $orderIds])->json();
    }

    /**
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function cancelAll(): array
    {
        return $this->httpClient->delete('/cancel-all')->json();
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function cancelMarketOrders(array $payload): array
    {
        return $this->httpClient->delete('/cancel-market-orders', $payload)->json();
    }
}
