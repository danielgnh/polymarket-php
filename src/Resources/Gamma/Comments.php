<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Resources\Gamma;

use Danielgnh\PolymarketPhp\Exceptions\PolymarketException;
use Danielgnh\PolymarketPhp\Resources\Resource;

class Comments extends Resource
{
    /**
     * List comments.
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws PolymarketException
     */
    public function list(): array
    {
        $response = $this->httpClient->get('/comments');

        /** @var array<int, array<string, mixed>> */
        return $response->json();
    }

    /**
     * Get comment by ID.
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function get(string $commentId): array
    {
        $response = $this->httpClient->get("/comments/$commentId");

        return $response->json();
    }

    /**
     * Get comments by user address.
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws PolymarketException
     */
    public function byUserAddress(string $userAddress): array
    {
        $response = $this->httpClient->get("/comments/user_address/$userAddress");

        /** @var array<int, array<string, mixed>> */
        return $response->json();
    }
}
