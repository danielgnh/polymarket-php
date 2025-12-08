<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Resources\Gamma;

use Danielgnh\PolymarketPhp\Exceptions\PolymarketException;
use Danielgnh\PolymarketPhp\Resources\Resource;

class Health extends Resource
{
    /**
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function check(): array
    {
        $response = $this->httpClient->get('/');

        return $response->json();
    }
}
