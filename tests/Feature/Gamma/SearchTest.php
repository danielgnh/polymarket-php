<?php

declare(strict_types=1);

use Danielgnh\PolymarketPhp\Client;
use Danielgnh\PolymarketPhp\Http\FakeGuzzleHttpClient;

beforeEach(function () {
    $this->fakeHttp = new FakeGuzzleHttpClient();
    $this->client = new Client(gammaHttpClient: $this->fakeHttp, clobHttpClient: $this->fakeHttp);
});

describe('Search::search()', function () {
    it('searches markets, events, and profiles', function () {
        $searchResults = [
            'markets' => [
                ['id' => 'market1', 'question' => 'Will Bitcoin reach $100k?', 'type' => 'market'],
            ],
            'events' => [
                ['id' => 'event1', 'title' => 'Bitcoin Predictions 2025', 'type' => 'event'],
            ],
            'profiles' => [],
        ];

        $this->fakeHttp->addJsonResponse('GET', '/public-search', $searchResults);

        $result = $this->client->gamma()->search()->search('Bitcoin');

        expect($result)->toBeArray()
            ->and($result)->toHaveKey('markets')
            ->and($result)->toHaveKey('events')
            ->and($result['markets'])->toHaveCount(1)
            ->and($result['markets'][0]['question'])->toContain('Bitcoin');
    });

    it('applies filters to search', function () {
        $searchResults = [
            'markets' => [
                ['id' => 'market1', 'question' => 'Will Bitcoin reach $100k?', 'active' => true],
            ],
            'events' => [],
            'profiles' => [],
        ];

        $this->fakeHttp->addJsonResponse('GET', '/public-search', $searchResults);

        $result = $this->client->gamma()->search()->search('Bitcoin', filters: ['active' => true]);

        expect($result)->toBeArray()
            ->and($result['markets'])->toBeArray();
    });

    it('handles empty search results', function () {
        $searchResults = [
            'markets' => [],
            'events' => [],
            'profiles' => [],
        ];

        $this->fakeHttp->addJsonResponse('GET', '/public-search', $searchResults);

        $result = $this->client->gamma()->search()->search('NonexistentQuery');

        expect($result)->toBeArray()
            ->and($result['markets'])->toBeEmpty()
            ->and($result['events'])->toBeEmpty()
            ->and($result['profiles'])->toBeEmpty();
    });

    it('handles search with only markets', function () {
        $searchResults = [
            'markets' => [
                ['id' => 'market1', 'question' => 'Election 2024?'],
                ['id' => 'market2', 'question' => 'Who will win?'],
            ],
            'events' => [],
            'profiles' => [],
        ];

        $this->fakeHttp->addJsonResponse('GET', '/public-search', $searchResults);

        $result = $this->client->gamma()->search()->search('election');

        expect($result)->toBeArray()
            ->and($result['markets'])->toHaveCount(2)
            ->and($result['events'])->toBeEmpty();
    });
});
