<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Resources\Gamma;

use Danielgnh\PolymarketPhp\Exceptions\PolymarketException;
use Danielgnh\PolymarketPhp\Resources\Resource;

class Tags extends Resource
{
    /**
     * List all tags.
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws PolymarketException
     */
    public function list(): array
    {
        $response = $this->httpClient->get('/tags');

        /** @var array<int, array<string, mixed>> */
        return $response->json();
    }

    /**
     * Get specific tag by ID.
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function get(string $tagId): array
    {
        $response = $this->httpClient->get("/tags/$tagId");

        return $response->json();
    }

    /**
     * Get tag by slug identifier.
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function getBySlug(string $slug): array
    {
        $response = $this->httpClient->get("/tags/slug/$slug");

        return $response->json();
    }

    /**
     * Get related tags by ID.
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws PolymarketException
     */
    public function relatedTags(string $tagId): array
    {
        $response = $this->httpClient->get("/tags/$tagId/related-tags");

        /** @var array<int, array<string, mixed>> */
        return $response->json();
    }

    /**
     * Get related tags by slug.
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws PolymarketException
     */
    public function relatedTagsBySlug(string $slug): array
    {
        $response = $this->httpClient->get("/tags/slug/$slug/related-tags");

        /** @var array<int, array<string, mixed>> */
        return $response->json();
    }

    /**
     * Get tags related to a tag ID.
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws PolymarketException
     */
    public function getTagsRelatedToTag(string $tagId): array
    {
        $response = $this->httpClient->get("/tags/$tagId/related-tags/tags");

        /** @var array<int, array<string, mixed>> */
        return $response->json();
    }

    /**
     * Get tags related to a tag slug.
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws PolymarketException
     */
    public function getTagsRelatedToTagBySlug(string $slug): array
    {
        $response = $this->httpClient->get("/tags/slug/$slug/related-tags/tags");

        /** @var array<int, array<string, mixed>> */
        return $response->json();
    }
}
