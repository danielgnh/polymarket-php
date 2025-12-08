<?php

declare(strict_types=1);

use Danielgnh\PolymarketPhp\Client;
use Danielgnh\PolymarketPhp\Http\FakeGuzzleHttpClient;

beforeEach(function () {
    $this->fakeHttp = new FakeGuzzleHttpClient();
    $this->client = new Client(gammaHttpClient: $this->fakeHttp, clobHttpClient: $this->fakeHttp);
});

describe('Sports::list()', function () {
    it('retrieves sports metadata', function () {
        $sportsData = [
            [
                'id' => 'sport1',
                'name' => 'Basketball',
                'image' => 'https://example.com/basketball.png',
                'ordering' => 1,
            ],
            [
                'id' => 'sport2',
                'name' => 'Football',
                'image' => 'https://example.com/football.png',
                'ordering' => 2,
            ],
        ];

        $this->fakeHttp->addJsonResponse('GET', '/sports', $sportsData);

        $result = $this->client->gamma()->sports()->list();

        expect($result)->toBeArray()
            ->and($result)->toHaveCount(2)
            ->and($result[0]['name'])->toBe('Basketball')
            ->and($result[1]['name'])->toBe('Football');
    });

    it('handles empty sports list', function () {
        $this->fakeHttp->addJsonResponse('GET', '/sports', []);

        $result = $this->client->gamma()->sports()->list();

        expect($result)->toBeArray()
            ->and($result)->toBeEmpty();
    });
});

describe('Sports::teams()', function () {
    it('lists teams', function () {
        $teamsData = [
            [
                'id' => 'team1',
                'name' => 'Los Angeles Lakers',
                'sport' => 'basketball',
            ],
            [
                'id' => 'team2',
                'name' => 'Boston Celtics',
                'sport' => 'basketball',
            ],
        ];

        $this->fakeHttp->addJsonResponse('GET', '/teams', $teamsData);

        $result = $this->client->gamma()->sports()->teams();

        expect($result)->toBeArray()
            ->and($result)->toHaveCount(2)
            ->and($result[0]['name'])->toBe('Los Angeles Lakers')
            ->and($result[1]['sport'])->toBe('basketball');
    });
});
