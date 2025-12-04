<?php

declare(strict_types=1);

use Danielgnh\PolymarketPhp\Client;
use Danielgnh\PolymarketPhp\Http\FakeGuzzleHttpClient;

beforeEach(function () {
    $this->fakeHttp = new FakeGuzzleHttpClient();
    $this->client = new Client(clobHttpClient: $this->fakeHttp);
});

describe('Pricing::getPrice()', function () {
    it('fetches price for a token and side', function () {
        $priceData = ['price' => '0.55'];

        $this->fakeHttp->addJsonResponse('GET', '/price', $priceData);

        $result = $this->client->clob()->pricing()->getPrice('token_123', 'buy');

        expect($result)->toBeArray()
            ->and($result)->toHaveKey('price')
            ->and($result['price'])->toBe('0.55');
    });
});

describe('Pricing::getPrices()', function () {
    it('fetches multiple prices', function () {
        $pricesData = [
            ['token_id' => 'token_1', 'price' => '0.52'],
            ['token_id' => 'token_2', 'price' => '0.48'],
        ];

        $this->fakeHttp->addJsonResponse('POST', '/prices', $pricesData);

        $result = $this->client->clob()->pricing()->getPrices([
            ['token_id' => 'token_1', 'side' => 'buy'],
            ['token_id' => 'token_2', 'side' => 'sell'],
        ]);

        expect($result)->toBeArray()
            ->and($result)->toHaveCount(2);
    });
});

describe('Pricing::getMidpoint()', function () {
    it('fetches midpoint price for a token', function () {
        $midpointData = ['midpoint' => '0.525'];

        $this->fakeHttp->addJsonResponse('GET', '/midpoint', $midpointData);

        $result = $this->client->clob()->pricing()->getMidpoint('token_123');

        expect($result)->toBeArray()
            ->and($result)->toHaveKey('midpoint')
            ->and($result['midpoint'])->toBe('0.525');
    });
});

describe('Pricing::getMidpoints()', function () {
    it('fetches multiple midpoints', function () {
        $midpointsData = [
            ['token_id' => 'token_1', 'midpoint' => '0.51'],
            ['token_id' => 'token_2', 'midpoint' => '0.49'],
        ];

        $this->fakeHttp->addJsonResponse('POST', '/midpoints', $midpointsData);

        $result = $this->client->clob()->pricing()->getMidpoints([
            ['token_id' => 'token_1'],
            ['token_id' => 'token_2'],
        ]);

        expect($result)->toBeArray()
            ->and($result)->toHaveCount(2);
    });
});

describe('Pricing::getLastTradePrice()', function () {
    it('fetches last trade price for a token', function () {
        $lastPriceData = ['price' => '0.53', 'timestamp' => 1234567890];

        $this->fakeHttp->addJsonResponse('GET', '/last-trade-price', $lastPriceData);

        $result = $this->client->clob()->pricing()->getLastTradePrice('token_123');

        expect($result)->toBeArray()
            ->and($result)->toHaveKey('price')
            ->and($result['price'])->toBe('0.53');
    });
});

describe('Pricing::getLastTradesPrices()', function () {
    it('fetches last trade prices for multiple tokens', function () {
        $lastPricesData = [
            ['token_id' => 'token_1', 'price' => '0.52'],
            ['token_id' => 'token_2', 'price' => '0.48'],
        ];

        $this->fakeHttp->addJsonResponse('POST', '/last-trades-prices', $lastPricesData);

        $result = $this->client->clob()->pricing()->getLastTradesPrices([
            ['token_id' => 'token_1'],
            ['token_id' => 'token_2'],
        ]);

        expect($result)->toBeArray()
            ->and($result)->toHaveCount(2);
    });
});

describe('Pricing::getPricesHistory()', function () {
    it('fetches historical price data', function () {
        $historyData = [
            ['timestamp' => 1234567890, 'price' => '0.52'],
            ['timestamp' => 1234567900, 'price' => '0.53'],
            ['timestamp' => 1234567910, 'price' => '0.54'],
        ];

        $this->fakeHttp->addJsonResponse('GET', '/prices-history', $historyData);

        $result = $this->client->clob()->pricing()->getPricesHistory([
            'token_id' => 'token_123',
            'start' => 1234567890,
            'end' => 1234567910,
        ]);

        expect($result)->toBeArray()
            ->and($result)->toHaveCount(3);
    });
});
