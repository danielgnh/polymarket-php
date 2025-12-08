<?php

declare(strict_types=1);

use Danielgnh\PolymarketPhp\Client;
use Danielgnh\PolymarketPhp\Http\FakeGuzzleHttpClient;

beforeEach(function () {
    $this->fakeHttp = new FakeGuzzleHttpClient();
    $this->client = new Client(gammaHttpClient: $this->fakeHttp, clobHttpClient: $this->fakeHttp);
});

describe('Series::list()', function () {
    it('lists series', function () {
        $seriesData = [
            [
                'id' => 'series1',
                'title' => 'NBA Finals 2024',
                'description' => 'Markets for the NBA Finals',
            ],
            [
                'id' => 'series2',
                'title' => 'World Cup 2026',
                'description' => 'World Cup prediction markets',
            ],
        ];

        $this->fakeHttp->addJsonResponse('GET', '/series', $seriesData);

        $result = $this->client->gamma()->series()->list();

        expect($result)->toBeArray()
            ->and($result)->toHaveCount(2)
            ->and($result[0]['title'])->toBe('NBA Finals 2024');
    });

    it('handles empty series list', function () {
        $this->fakeHttp->addJsonResponse('GET', '/series', []);

        $result = $this->client->gamma()->series()->list();

        expect($result)->toBeArray()
            ->and($result)->toBeEmpty();
    });
});

describe('Series::get()', function () {
    it('gets series by ID', function () {
        $seriesData = [
            'id' => 'series1',
            'title' => 'NBA Finals 2024',
            'description' => 'Markets for the NBA Finals',
            'markets' => ['market1', 'market2', 'market3'],
        ];

        $this->fakeHttp->addJsonResponse('GET', '/series/series1', $seriesData);

        $result = $this->client->gamma()->series()->get('series1');

        expect($result)->toBeArray()
            ->and($result['id'])->toBe('series1')
            ->and($result['title'])->toBe('NBA Finals 2024')
            ->and($result['markets'])->toHaveCount(3);
    });
});
