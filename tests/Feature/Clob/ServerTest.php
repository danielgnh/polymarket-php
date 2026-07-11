<?php

declare(strict_types=1);

use PolymarketPhp\Polymarket\Client;
use PolymarketPhp\Polymarket\Http\FakeGuzzleHttpClient;
use PolymarketPhp\Polymarket\Http\Response;

beforeEach(function (): void {
    $this->fakeHttp = new FakeGuzzleHttpClient();
    $this->client = new Client(clobHttpClient: $this->fakeHttp);
});

describe('Server::healthCheck()', function (): void {
    it('performs health check', function (): void {
        $healthData = "OK";

        $this->fakeHttp->addResponse('GET', '/', new Response(200, [], $healthData));

        $result = $this->client->clob()->server()->healthCheck();

        expect($result)->toBe('OK');
    });
});

describe('Server::getTime()', function (): void {
    it('retrieves current server timestamp', function (): void {
        $timeData = "1234567890";

        $this->fakeHttp->addResponse('GET', '/time', new Response(200, [], $timeData));

        $result = $this->client->clob()->server()->getTime();

        expect($result)->toBe(1234567890);
    });
});

describe('Server::getVersion()', function (): void {
    it('retrieves the active order version from the CLOB API', function (): void {
        $this->fakeHttp->addJsonResponse('GET', '/version', ['version' => 2]);

        $result = $this->client->clob()->server()->getVersion();

        expect($result)->toBe(2);
    });

    it('defaults to version 2 when the API omits the version field', function (): void {
        $this->fakeHttp->addJsonResponse('GET', '/version', []);

        $result = $this->client->clob()->server()->getVersion();

        expect($result)->toBe(2);
    });
});

describe('Server::getFeeRate()', function (): void {
    it('fetches fee rate for token', function (): void {
        $feeData = ['fee_rate_bps' => 10, 'fee_percentage' => '0.10'];

        $this->fakeHttp->addJsonResponse('GET', '/fee-rate', $feeData);

        $result = $this->client->clob()->server()->getFeeRate('token_123');

        expect($result)->toBeArray()
            ->and($result)->toHaveKey('fee_rate_bps')
            ->and($result['fee_rate_bps'])->toBe(10);
    });

    it('preserves decimal precision in fee rates', function (): void {
        $feeData = ['fee_rate_bps' => 15, 'fee_percentage' => '0.15'];

        $this->fakeHttp->addJsonResponse('GET', '/fee-rate', $feeData);

        $result = $this->client->clob()->server()->getFeeRate('token_456');

        expect($result['fee_percentage'])->toBe('0.15');
    });
});
