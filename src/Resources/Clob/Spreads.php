<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Resources\Clob;

use Danielgnh\PolymarketPhp\Exceptions\PolymarketException;
use Danielgnh\PolymarketPhp\Resources\Resource;

class Spreads extends Resource
{
    /**
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function get(string $tokenId): array
    {
        return $this->httpClient->get('/spread', ['token_id' => $tokenId])->json();
    }

    /**
     * @param array<int, array{token_id: string}> $params
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws PolymarketException
     */
    public function getMultiple(array $params): array
    {
        return $this->httpClient->post('/spreads', $params)->json();
    }
}
