<?php

declare(strict_types=1);

use Danielgnh\PolymarketPhp\Client;
use Danielgnh\PolymarketPhp\Http\FakeGuzzleHttpClient;

beforeEach(function () {
    $this->fakeHttp = new FakeGuzzleHttpClient();
    $this->client = new Client(gammaHttpClient: $this->fakeHttp, clobHttpClient: $this->fakeHttp);
});

describe('Tags::list()', function () {
    it('lists all tags', function () {
        $tagsData = [
            ['id' => 'tag1', 'label' => 'Crypto', 'slug' => 'crypto'],
            ['id' => 'tag2', 'label' => 'Politics', 'slug' => 'politics'],
            ['id' => 'tag3', 'label' => 'Sports', 'slug' => 'sports'],
        ];

        $this->fakeHttp->addJsonResponse('GET', '/tags', $tagsData);

        $result = $this->client->gamma()->tags()->list();

        expect($result)->toBeArray()
            ->and($result)->toHaveCount(3)
            ->and($result[0]['label'])->toBe('Crypto');
    });
});

describe('Tags::get()', function () {
    it('gets tag by ID', function () {
        $tagData = ['id' => 'tag1', 'label' => 'Crypto', 'slug' => 'crypto'];

        $this->fakeHttp->addJsonResponse('GET', '/tags/tag1', $tagData);

        $result = $this->client->gamma()->tags()->get('tag1');

        expect($result)->toBeArray()
            ->and($result['id'])->toBe('tag1')
            ->and($result['label'])->toBe('Crypto');
    });
});

describe('Tags::getBySlug()', function () {
    it('gets tag by slug', function () {
        $tagData = ['id' => 'tag1', 'label' => 'Crypto', 'slug' => 'crypto'];

        $this->fakeHttp->addJsonResponse('GET', '/tags/slug/crypto', $tagData);

        $result = $this->client->gamma()->tags()->getBySlug('crypto');

        expect($result)->toBeArray()
            ->and($result['slug'])->toBe('crypto')
            ->and($result['label'])->toBe('Crypto');
    });
});

describe('Tags::relatedTags()', function () {
    it('gets related tags by ID', function () {
        $relatedTagsData = [
            ['id' => 'tag2', 'label' => 'Bitcoin'],
            ['id' => 'tag3', 'label' => 'Ethereum'],
        ];

        $this->fakeHttp->addJsonResponse('GET', '/tags/tag1/related-tags', $relatedTagsData);

        $result = $this->client->gamma()->tags()->relatedTags('tag1');

        expect($result)->toBeArray()
            ->and($result)->toHaveCount(2)
            ->and($result[0]['label'])->toBe('Bitcoin');
    });
});

describe('Tags::relatedTagsBySlug()', function () {
    it('gets related tags by slug', function () {
        $relatedTagsData = [
            ['id' => 'tag2', 'label' => 'Bitcoin'],
            ['id' => 'tag3', 'label' => 'Ethereum'],
        ];

        $this->fakeHttp->addJsonResponse('GET', '/tags/slug/crypto/related-tags', $relatedTagsData);

        $result = $this->client->gamma()->tags()->relatedTagsBySlug('crypto');

        expect($result)->toBeArray()
            ->and($result)->toHaveCount(2);
    });
});

describe('Tags::relatedTagsTags()', function () {
    it('gets tags related to a tag ID', function () {
        $relatedTagsData = [
            ['id' => 'tag4', 'label' => 'DeFi'],
            ['id' => 'tag5', 'label' => 'NFT'],
        ];

        $this->fakeHttp->addJsonResponse('GET', '/tags/tag1/related-tags/tags', $relatedTagsData);

        $result = $this->client->gamma()->tags()->relatedTagsTags('tag1');

        expect($result)->toBeArray()
            ->and($result)->toHaveCount(2);
    });
});

describe('Tags::relatedTagsTagsBySlug()', function () {
    it('gets tags related to a tag slug', function () {
        $relatedTagsData = [
            ['id' => 'tag4', 'label' => 'DeFi'],
            ['id' => 'tag5', 'label' => 'NFT'],
        ];

        $this->fakeHttp->addJsonResponse('GET', '/tags/slug/crypto/related-tags/tags', $relatedTagsData);

        $result = $this->client->gamma()->tags()->relatedTagsTagsBySlug('crypto');

        expect($result)->toBeArray()
            ->and($result)->toHaveCount(2);
    });
});
