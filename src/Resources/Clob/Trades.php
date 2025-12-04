<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Resources\Clob;

use Danielgnh\PolymarketPhp\Exceptions\PolymarketException;
use Danielgnh\PolymarketPhp\Resources\Resource;

class Trades extends Resource
{
    /**
     * @param array<string, mixed> $params
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws PolymarketException
     */
    public function list(array $params = []): array
    {
        return $this->httpClient->get('/trades', $params)->json();
    }

    /**
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function listPaginated(array $params = []): array
    {
        return $this->httpClient->get('/trades', $params)->json();
    }

    /**
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function getBuilderTrades(array $params = []): array
    {
        return $this->httpClient->get('/builder-trades', $params)->json();
    }
}
