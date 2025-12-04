<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Resources\Clob;

use Danielgnh\PolymarketPhp\Exceptions\PolymarketException;
use Danielgnh\PolymarketPhp\Resources\Resource;

class Pricing extends Resource
{
    /**
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function getPrice(string $tokenId, string $side): array
    {
        return $this->httpClient->get('/price', [
            'token_id' => $tokenId,
            'side' => $side,
        ])->json();
    }

    /**
     * @param array<int, array{token_id: string, side: string}> $params
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws PolymarketException
     */
    public function getPrices(array $params): array
    {
        return $this->httpClient->post('/prices', $params)->json();
    }

    /**
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function getMidpoint(string $tokenId): array
    {
        return $this->httpClient->get('/midpoint', ['token_id' => $tokenId])->json();
    }

    /**
     * @param array<int, array{token_id: string}> $params
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws PolymarketException
     */
    public function getMidpoints(array $params): array
    {
        return $this->httpClient->post('/midpoints', $params)->json();
    }

    /**
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function getLastTradePrice(string $tokenId): array
    {
        return $this->httpClient->get('/last-trade-price', ['token_id' => $tokenId])->json();
    }

    /**
     * @param array<int, array{token_id: string}> $params
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws PolymarketException
     */
    public function getLastTradesPrices(array $params): array
    {
        return $this->httpClient->post('/last-trades-prices', $params)->json();
    }

    /**
     * @param array<string, mixed> $params
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws PolymarketException
     */
    public function getPricesHistory(array $params = []): array
    {
        return $this->httpClient->get('/prices-history', $params)->json();
    }
}
