<?php

declare(strict_types=1);

use Danielgnh\PolymarketPhp\Client;
use Danielgnh\PolymarketPhp\Http\FakeGuzzleHttpClient;

beforeEach(function () {
    $this->fakeHttp = new FakeGuzzleHttpClient();
    $this->client = new Client(clobHttpClient: $this->fakeHttp);
});

describe('Authentication::createApiKey()', function () {
    it('creates a new API key', function () {
        $apiKeyData = [
            'api_key' => 'test_key_123',
            'api_secret' => 'test_secret_456',
            'passphrase' => 'test_passphrase',
        ];

        $this->fakeHttp->addJsonResponse('POST', '/create-api-key', $apiKeyData, 201);

        $result = $this->client->clob()->authentication()->createApiKey();

        expect($result)->toBeArray()
            ->and($result)->toHaveKey('api_key')
            ->and($result['api_key'])->toBe('test_key_123');
    });
});

describe('Authentication::deriveApiKey()', function () {
    it('derives existing API credentials', function () {
        $apiKeyData = [
            'api_key' => 'derived_key_123',
            'api_secret' => 'derived_secret_456',
        ];

        $this->fakeHttp->addJsonResponse('GET', '/derive-api-key', $apiKeyData);

        $result = $this->client->clob()->authentication()->deriveApiKey();

        expect($result)->toBeArray()
            ->and($result)->toHaveKey('api_key')
            ->and($result['api_key'])->toBe('derived_key_123');
    });
});

describe('Authentication::getApiKeys()', function () {
    it('lists all API keys', function () {
        $apiKeysData = [
            'keys' => [
                ['api_key' => 'key_1', 'created_at' => '2025-01-01T00:00:00Z'],
                ['api_key' => 'key_2', 'created_at' => '2025-01-02T00:00:00Z'],
            ],
        ];

        $this->fakeHttp->addJsonResponse('GET', '/api-keys', $apiKeysData);

        $result = $this->client->clob()->authentication()->getApiKeys();

        expect($result)->toBeArray()
            ->and($result)->toHaveKey('keys')
            ->and($result['keys'])->toHaveCount(2);
    });
});

describe('Authentication::deleteApiKey()', function () {
    it('revokes current API key', function () {
        $deleteResponse = ['success' => true, 'message' => 'API key deleted'];

        $this->fakeHttp->addJsonResponse('DELETE', '/api-key', $deleteResponse);

        $result = $this->client->clob()->authentication()->deleteApiKey();

        expect($result)->toBeArray()
            ->and($result['success'])->toBeTrue();
    });
});
