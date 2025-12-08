<?php

declare(strict_types=1);

use Danielgnh\PolymarketPhp\Client;
use Danielgnh\PolymarketPhp\Http\FakeGuzzleHttpClient;

beforeEach(function () {
    $this->fakeHttp = new FakeGuzzleHttpClient();
    $this->client = new Client(gammaHttpClient: $this->fakeHttp, clobHttpClient: $this->fakeHttp);
});

describe('Markets::list()', function () {
    it('fetches list of markets successfully', function () {
        $marketsData = $this->loadFixture('markets_list.json');

        $this->fakeHttp->addJsonResponse('GET', '/markets', $marketsData);

        $result = $this->client->gamma()->markets()->list();

        expect($result)->toBeArray()
            ->and($result)->toHaveCount(3)
            ->and($result[0])->toHaveKey('id')
            ->and($result[0])->toHaveKey('question')
            ->and($result[0]['id'])->toBe('0x1234567890abcdef');
    });

    it('applies limit parameter correctly', function () {
        $marketsData = $this->loadFixture('markets_list.json');

        $this->fakeHttp->addJsonResponse('GET', '/markets', array_slice($marketsData, 0, 2));

        $result = $this->client->gamma()->markets()->list(limit: 2);

        expect($result)->toHaveCount(2);

        // Verify the request was made
        expect($this->fakeHttp->hasRequest('GET', '/markets'))->toBeTrue();
    });

    it('applies offset parameter for pagination', function () {
        $marketsData = $this->loadFixture('markets_list.json');

        $this->fakeHttp->addJsonResponse('GET', '/markets', array_slice($marketsData, 1));

        $result = $this->client->gamma()->markets()->list(offset: 1);

        expect($result)->toHaveCount(2)
            ->and($result[0]['id'])->toBe('0xfedcba0987654321');
    });

    it('applies custom filters', function () {
        $marketsData = $this->loadFixture('markets_list.json');
        $filteredData = array_filter($marketsData, fn ($m) => in_array('crypto', $m['tags']));

        $this->fakeHttp->addJsonResponse('GET', '/markets', array_values($filteredData));

        $result = $this->client->gamma()->markets()->list(filters: ['tag' => 'crypto']);

        expect($result)->toBeArray()
            ->and(count($result))->toBeGreaterThan(0);

        foreach ($result as $market) {
            expect($market['tags'])->toContain('crypto');
        }
    });

    it('handles empty markets list', function () {
        $this->fakeHttp->addJsonResponse('GET', '/markets', []);

        $result = $this->client->gamma()->markets()->list();

        expect($result)->toBeArray()
            ->and($result)->toBeEmpty();
    });
});

describe('Markets::get()', function () {
    it('fetches single market by id', function () {
        $marketData = $this->loadFixture('market.json');

        $this->fakeHttp->addJsonResponse('GET', '/markets/0x1234567890abcdef', $marketData);

        $result = $this->client->gamma()->markets()->get('0x1234567890abcdef');

        expect($result)->toBeArray()
            ->and($result['id'])->toBe('0x1234567890abcdef')
            ->and($result['question'])->toBe('Will Bitcoin reach $100k by end of 2025?')
            ->and($result['outcomes'])->toBe(['Yes', 'No'])
            ->and($result['outcomePrices'])->toBe(['0.52', '0.48']);
    });

    it('handles market with all fields', function () {
        $marketData = $this->loadFixture('market.json');

        $this->fakeHttp->addJsonResponse('GET', '/markets/0x1234567890abcdef', $marketData);

        $result = $this->client->gamma()->markets()->get('0x1234567890abcdef');

        expect($result)->toHaveKeys([
            'id',
            'question',
            'description',
            'outcomes',
            'outcomePrices',
            'volume',
            'liquidity',
            'endDate',
            'active',
            'closed',
            'tags',
        ]);
    });

    it('preserves decimal precision in prices', function () {
        $marketData = $this->loadFixture('market.json');

        $this->fakeHttp->addJsonResponse('GET', '/markets/0x1234567890abcdef', $marketData);

        $result = $this->client->gamma()->markets()->get('0x1234567890abcdef');

        // Verify prices are strings (not floats)
        expect($result['outcomePrices'][0])->toBeString()
            ->and($result['outcomePrices'][0])->toBe('0.52')
            ->and($result['volume'])->toBeString()
            ->and($result['volume'])->toBe('1234567.89');
    });
});

describe('Markets::getBySlug()', function () {
    it('fetches market by slug', function () {
        $marketData = $this->loadFixture('market.json');

        $this->fakeHttp->addJsonResponse('GET', '/markets/slug/bitcoin-100k-2025', $marketData);

        $result = $this->client->gamma()->markets()->getBySlug('bitcoin-100k-2025');

        expect($result)->toBeArray()
            ->and($result['id'])->toBe('0x1234567890abcdef')
            ->and($result['question'])->toBe('Will Bitcoin reach $100k by end of 2025?');
    });
});

describe('Markets::tags()', function () {
    it('fetches market tags', function () {
        $tagsData = [
            ['id' => 'tag1', 'label' => 'Crypto'],
            ['id' => 'tag2', 'label' => 'Bitcoin'],
        ];

        $this->fakeHttp->addJsonResponse('GET', '/markets/0x1234567890abcdef/tags', $tagsData);

        $result = $this->client->gamma()->markets()->tags('0x1234567890abcdef');

        expect($result)->toBeArray()
            ->and($result)->toHaveCount(2)
            ->and($result[0]['label'])->toBe('Crypto');
    });
});

describe('Markets integration scenarios', function () {
    it('can fetch list and then get individual market', function () {
        // First, list markets
        $listData = $this->loadFixture('markets_list.json');
        $this->fakeHttp->addJsonResponse('GET', '/markets', $listData);

        $markets = $this->client->gamma()->markets()->list(limit: 5);

        expect($markets)->toBeArray()
            ->and($markets)->not->toBeEmpty();

        // Then fetch first market details
        $firstMarketId = $markets[0]['id'];
        $marketData = $this->loadFixture('market.json');
        $this->fakeHttp->addJsonResponse('GET', "/markets/{$firstMarketId}", $marketData);

        $marketDetails = $this->client->gamma()->markets()->get($firstMarketId);

        expect($marketDetails)->toBeArray()
            ->and($marketDetails['id'])->toBe($firstMarketId);

        // Verify both requests were made
        expect($this->fakeHttp->hasRequest('GET', '/markets'))->toBeTrue();
        expect($this->fakeHttp->hasRequest('GET', "/markets/{$firstMarketId}"))->toBeTrue();
    });
});
