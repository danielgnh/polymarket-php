<?php

declare(strict_types=1);

use Danielgnh\PolymarketPhp\Client;
use Danielgnh\PolymarketPhp\Http\FakeGuzzleHttpClient;

beforeEach(function () {
    $this->fakeHttp = new FakeGuzzleHttpClient();
    $this->client = new Client(clobHttpClient: $this->fakeHttp);
});

describe('Server::healthCheck()', function () {
    it('performs health check', function () {
        $healthData = ['status' => 'ok', 'timestamp' => 1234567890];

        $this->fakeHttp->addJsonResponse('GET', '/', $healthData);

        $result = $this->client->clob()->server()->healthCheck();

        expect($result)->toBeArray()
            ->and($result['status'])->toBe('ok');
    });
});

describe('Server::getTime()', function () {
    it('retrieves current server timestamp', function () {
        $timeData = ['timestamp' => 1234567890, 'iso' => '2025-01-15T12:00:00Z'];

        $this->fakeHttp->addJsonResponse('GET', '/time', $timeData);

        $result = $this->client->clob()->server()->getTime();

        expect($result)->toBeArray()
            ->and($result)->toHaveKey('timestamp')
            ->and($result['timestamp'])->toBe(1234567890);
    });
});

describe('Server::getFeeRate()', function () {
    it('fetches fee rate for token', function () {
        $feeData = ['fee_rate_bps' => 10, 'fee_percentage' => '0.10'];

        $this->fakeHttp->addJsonResponse('GET', '/fee-rate', $feeData);

        $result = $this->client->clob()->server()->getFeeRate('token_123');

        expect($result)->toBeArray()
            ->and($result)->toHaveKey('fee_rate_bps')
            ->and($result['fee_rate_bps'])->toBe(10);
    });

    it('preserves decimal precision in fee rates', function () {
        $feeData = ['fee_rate_bps' => 15, 'fee_percentage' => '0.15'];

        $this->fakeHttp->addJsonResponse('GET', '/fee-rate', $feeData);

        $result = $this->client->clob()->server()->getFeeRate('token_456');

        expect($result['fee_percentage'])->toBe('0.15');
    });
});
