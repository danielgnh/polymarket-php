<?php

declare(strict_types=1);

use Danielgnh\PolymarketPhp\Client;
use Danielgnh\PolymarketPhp\Http\FakeGuzzleHttpClient;

beforeEach(function () {
    $this->fakeHttp = new FakeGuzzleHttpClient();
    $this->client = new Client(clobHttpClient: $this->fakeHttp);
});

describe('Spreads::get()', function () {
    it('fetches spread for a token', function () {
        $spreadData = ['spread' => '0.02', 'bid' => '0.51', 'ask' => '0.53'];

        $this->fakeHttp->addJsonResponse('GET', '/spread', $spreadData);

        $result = $this->client->clob()->spreads()->get('token_123');

        expect($result)->toBeArray()
            ->and($result)->toHaveKey('spread')
            ->and($result['spread'])->toBe('0.02');
    });

    it('preserves decimal precision in spreads', function () {
        $spreadData = [
            'spread' => '0.0123456789',
            'bid' => '0.4876543211',
            'ask' => '0.5000000000',
        ];

        $this->fakeHttp->addJsonResponse('GET', '/spread', $spreadData);

        $result = $this->client->clob()->spreads()->get('token_123');

        expect($result['spread'])->toBe('0.0123456789')
            ->and($result['bid'])->toBe('0.4876543211')
            ->and($result['ask'])->toBe('0.5000000000');
    });
});

describe('Spreads::getMultiple()', function () {
    it('fetches spreads for multiple tokens', function () {
        $spreadsData = [
            ['token_id' => 'token_1', 'spread' => '0.02'],
            ['token_id' => 'token_2', 'spread' => '0.03'],
        ];

        $this->fakeHttp->addJsonResponse('POST', '/spreads', $spreadsData);

        $result = $this->client->clob()->spreads()->getMultiple([
            ['token_id' => 'token_1'],
            ['token_id' => 'token_2'],
        ]);

        expect($result)->toBeArray()
            ->and($result)->toHaveCount(2);
    });
});
