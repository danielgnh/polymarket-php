<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Resources\Gamma;

use Danielgnh\PolymarketPhp\Exceptions\PolymarketException;
use Danielgnh\PolymarketPhp\Resources\Resource;

class Sports extends Resource
{
    /**
     * Retrieves metadata for various sports including images, resolution sources,
     * ordering preferences, tags, and series information.
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws PolymarketException
     */
    public function list(): array
    {
        $response = $this->httpClient->get('/sports');

        /** @var array<int, array<string, mixed>> */
        return $response->json();
    }

    /**
     * List teams.
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws PolymarketException
     */
    public function teams(): array
    {
        $response = $this->httpClient->get('/teams');

        /** @var array<int, array<string, mixed>> */
        return $response->json();
    }
}
