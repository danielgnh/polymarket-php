<?php

declare(strict_types=1);

use Danielgnh\PolymarketPhp\Client;
use Danielgnh\PolymarketPhp\Http\FakeGuzzleHttpClient;

beforeEach(function () {
    $this->fakeHttp = new FakeGuzzleHttpClient();
    $this->client = new Client(clobHttpClient: $this->fakeHttp);
});

describe('Trades::list()', function () {
    it('fetches trade history', function () {
        $tradesData = [
            ['id' => 'trade_1', 'price' => '0.52', 'size' => '100.00', 'side' => 'buy'],
            ['id' => 'trade_2', 'price' => '0.53', 'size' => '50.00', 'side' => 'sell'],
        ];

        $this->fakeHttp->addJsonResponse('GET', '/trades', $tradesData);

        $result = $this->client->clob()->trades()->list();

        expect($result)->toBeArray()
            ->and($result)->toHaveCount(2)
            ->and($result[0])->toHaveKey('id')
            ->and($result[0]['price'])->toBe('0.52');
    });

    it('accepts filter parameters', function () {
        $tradesData = [
            ['id' => 'trade_1', 'market_id' => 'market_123', 'price' => '0.52'],
        ];

        $this->fakeHttp->addJsonResponse('GET', '/trades', $tradesData);

        $result = $this->client->clob()->trades()->list(['market_id' => 'market_123']);

        expect($result)->toBeArray()
            ->and($result[0]['market_id'])->toBe('market_123');
    });
});

describe('Trades::getBuilderTrades()', function () {
    it('fetches builder trade history', function () {
        $builderTradesData = [
            'trades' => [
                ['id' => 'builder_trade_1', 'price' => '0.55'],
            ],
        ];

        $this->fakeHttp->addJsonResponse('GET', '/builder-trades', $builderTradesData);

        $result = $this->client->clob()->trades()->getBuilderTrades();

        expect($result)->toBeArray()
            ->and($result)->toHaveKey('trades');
    });
});
