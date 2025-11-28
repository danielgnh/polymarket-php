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
    public function get(string $token_id): array
    {
        return $this->httpClient->get('/book', ['token_id' => $token_id])->json();
    }

    // todo: there is one more way to get multiple books in one request, implement it here
}
