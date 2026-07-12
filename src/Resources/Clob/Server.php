<?php

declare(strict_types=1);

namespace PolymarketPhp\Polymarket\Resources\Clob;

use PolymarketPhp\Polymarket\Exceptions\PolymarketException;
use PolymarketPhp\Polymarket\Resources\Resource;

class Server extends Resource
{
    /**
     * @throws PolymarketException
     */
    public function healthCheck(): string
    {
        return $this->httpClient->get('/')->body();
    }

    /**
     * @throws PolymarketException
     */
    public function getTime(): int
    {
        return (int) $this->httpClient->get('/time')->body();
    }

    /**
     * Active order version served by the CLOB (2 when the API omits it).
     *
     * @throws PolymarketException
     */
    public function getVersion(): int
    {
        $data = $this->httpClient->get('/version')->json();
        $version = $data['version'] ?? 2;

        return is_numeric($version) ? (int) $version : 2;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function getFeeRate(string $tokenId): array
    {
        return $this->httpClient->get('/fee-rate', ['token_id' => $tokenId])->json();
    }
}
