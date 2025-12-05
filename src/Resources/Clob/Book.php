<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Resources\Clob;

use Danielgnh\PolymarketPhp\Exceptions\PolymarketException;
use Danielgnh\PolymarketPhp\Resources\Resource;

class Book extends Resource
{
    /**
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function get(string $tokenId): array
    {
        return $this->httpClient->get('/book', ['token_id' => $tokenId])->json();
    }

    /**
     * @param array<int, array{token_id: string}> $params
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function getMultiple(array $params): array
    {
        return $this->httpClient->post('/books', $params)->json();
    }

    /**
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function getTickSize(string $tokenId): array
    {
        return $this->httpClient->get('/tick-size', ['token_id' => $tokenId])->json();
    }

    /**
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function getNegRisk(string $tokenId): array
    {
        return $this->httpClient->get('/neg-risk', ['token_id' => $tokenId])->json();
    }
}
