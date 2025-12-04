<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Resources\Clob;

use Danielgnh\PolymarketPhp\Exceptions\PolymarketException;
use Danielgnh\PolymarketPhp\Resources\Resource;

class Markets extends Resource
{
    /**
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function list(array $params = []): array
    {
        return $this->httpClient->get('/markets', $params)->json();
    }

    /**
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function getSimplified(array $params = []): array
    {
        return $this->httpClient->get('/simplified-markets', $params)->json();
    }

    /**
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function getSampling(array $params = []): array
    {
        return $this->httpClient->get('/sampling-markets', $params)->json();
    }

    /**
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function getSamplingSimplified(array $params = []): array
    {
        return $this->httpClient->get('/sampling-simplified-markets', $params)->json();
    }

    /**
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function get(string $conditionId): array
    {
        return $this->httpClient->get("/market/{$conditionId}")->json();
    }

    /**
     * @return array<int, array<string, mixed>>
     *
     * @throws PolymarketException
     */
    public function getTradeEvents(string $conditionId): array
    {
        return $this->httpClient->get("/market-trades-events/{$conditionId}")->json();
    }
}
