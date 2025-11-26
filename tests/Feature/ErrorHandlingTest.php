<?php

declare(strict_types=1);

use Danielgnh\PolymarketPhp\Client;
use Danielgnh\PolymarketPhp\Exceptions\JsonParseException;
use Danielgnh\PolymarketPhp\Http\FakeGuzzleHttpClient;
use Danielgnh\PolymarketPhp\Http\Response;

beforeEach(function () {
    $this->fakeHttp = new FakeGuzzleHttpClient();
    $this->client = new Client(httpClient: $this->fakeHttp);
});

describe('JSON parsing errors', function () {
    it('throws JsonParseException when response contains invalid json', function () {
        // Mock invalid JSON response
        $invalidJsonResponse = new Response(
            statusCode: 200,
            headers: ['Content-Type' => 'application/json'],
            body: 'this is not valid json {'
        );

        $this->fakeHttp->addResponse('GET', '/markets', $invalidJsonResponse);

        $this->client->markets()->list();
    })->throws(JsonParseException::class);

    it('throws JsonParseException with helpful message', function () {
        $invalidJsonResponse = new Response(
            statusCode: 200,
            headers: ['Content-Type' => 'application/json'],
            body: 'invalid json'
        );

        $this->fakeHttp->addResponse('GET', '/orders', $invalidJsonResponse);

        try {
            $this->client->orders()->list();
            expect(false)->toBeTrue(); // Should not reach here
        } catch (JsonParseException $e) {
            expect($e->getMessage())->toContain('Failed to parse JSON response');
        }
    });

    it('includes partial response body in exception', function () {
        $invalidJsonResponse = new Response(
            statusCode: 200,
            headers: ['Content-Type' => 'application/json'],
            body: 'not json at all'
        );

        $this->fakeHttp->addResponse('GET', '/markets/abc123', $invalidJsonResponse);

        try {
            $this->client->markets()->get('abc123');
            expect(false)->toBeTrue(); // Should not reach here
        } catch (JsonParseException $e) {
            expect($e->getMessage())->toContain('not json at all');
        }
    });
});

describe('Response validation', function () {
    it('successfully processes valid json response', function () {
        $validData = ['markets' => []];
        $this->fakeHttp->addJsonResponse('GET', '/markets', $validData);

        $result = $this->client->markets()->list();

        expect($result)->toBeArray();
    });

    it('handles empty json response', function () {
        $this->fakeHttp->addJsonResponse('GET', '/markets', []);

        $result = $this->client->markets()->list();

        expect($result)->toBeArray()
            ->and($result)->toBeEmpty();
    });

    it('handles deeply nested json response', function () {
        $nestedData = [
            [
                'id' => 'test',
                'nested' => [
                    'deep' => [
                        'value' => 'works',
                    ],
                ],
            ],
        ];

        $this->fakeHttp->addJsonResponse('GET', '/markets', $nestedData);

        $result = $this->client->markets()->list();

        expect($result[0]['nested']['deep']['value'])->toBe('works');
    });
});

describe('Client error handling', function () {
    it('creates client with valid configuration', function () {
        $client = new Client('test-api-key');

        expect($client)->toBeInstanceOf(Client::class);
    });

    it('creates client without api key', function () {
        $client = new Client();

        expect($client)->toBeInstanceOf(Client::class);
    });

    it('handles custom configuration options', function () {
        $client = new Client('test-key', [
            'base_url' => 'https://custom-api.example.com',
            'timeout' => 60,
            'retries' => 5,
        ]);

        expect($client)->toBeInstanceOf(Client::class);
    });

    it('accepts custom http client', function () {
        $fakeHttp = new FakeGuzzleHttpClient();
        $client = new Client(httpClient: $fakeHttp);

        expect($client)->toBeInstanceOf(Client::class);
    });
});

describe('Resource error scenarios', function () {
    it('handles market not found gracefully', function () {
        // Mock 404 response
        $notFoundResponse = new Response(
            statusCode: 404,
            headers: ['Content-Type' => 'application/json'],
            body: json_encode(['error' => 'Market not found'])
        );

        $this->fakeHttp->addResponse('GET', '/markets/nonexistent', $notFoundResponse);

        $result = $this->client->markets()->get('nonexistent');

        expect($result)->toBeArray()
            ->and($result)->toHaveKey('error');
    });

    it('handles order not found gracefully', function () {
        $notFoundResponse = new Response(
            statusCode: 404,
            headers: ['Content-Type' => 'application/json'],
            body: json_encode(['error' => 'Order not found'])
        );

        $this->fakeHttp->addResponse('GET', '/orders/nonexistent', $notFoundResponse);

        $result = $this->client->orders()->get('nonexistent');

        expect($result)->toBeArray()
            ->and($result)->toHaveKey('error');
    });
});

describe('Edge cases', function () {
    it('handles large json response', function () {
        // Create large dataset
        $largeDataset = array_map(function ($i) {
            return [
                'id' => "market_{$i}",
                'question' => "Question {$i}",
                'description' => str_repeat("Description {$i} ", 100),
            ];
        }, range(1, 1000));

        $this->fakeHttp->addJsonResponse('GET', '/markets', $largeDataset);

        $result = $this->client->markets()->list();

        expect($result)->toBeArray()
            ->and($result)->toHaveCount(1000);
    });

    it('handles special characters in json', function () {
        $specialData = [
            [
                'id' => 'test',
                'question' => "Will 'special' chars work? & yes! 😀",
                'description' => 'Testing: <>&"\' characters',
            ],
        ];

        $this->fakeHttp->addJsonResponse('GET', '/markets', $specialData);

        $result = $this->client->markets()->list();

        expect($result[0]['question'])->toContain("'special'")
            ->and($result[0]['question'])->toContain('😀');
    });

    it('handles unicode characters correctly', function () {
        $unicodeData = [
            [
                'id' => 'test',
                'question' => '日本語テスト',
                'description' => 'Тест на русском',
            ],
        ];

        $this->fakeHttp->addJsonResponse('GET', '/markets', $unicodeData);

        $result = $this->client->markets()->list();

        expect($result[0]['question'])->toBe('日本語テスト')
            ->and($result[0]['description'])->toBe('Тест на русском');
    });

    it('handles null values in response', function () {
        $dataWithNulls = [
            [
                'id' => 'test',
                'question' => 'Test',
                'description' => null,
                'tags' => null,
            ],
        ];

        $this->fakeHttp->addJsonResponse('GET', '/markets', $dataWithNulls);

        $result = $this->client->markets()->list();

        expect($result[0]['description'])->toBeNull()
            ->and($result[0]['tags'])->toBeNull();
    });
});

describe('Decimal precision edge cases', function () {
    it('preserves exact decimal values in prices', function () {
        $preciseData = [
            'id' => 'test',
            'outcomePrices' => ['0.123456789012345', '0.876543210987655'],
            'volume' => '12345678901234.567890',
        ];

        $this->fakeHttp->addJsonResponse('GET', '/markets/test', $preciseData);

        $result = $this->client->markets()->get('test');

        expect($result['outcomePrices'][0])->toBe('0.123456789012345')
            ->and($result['volume'])->toBe('12345678901234.567890');
    });

    it('handles zero values correctly', function () {
        $zeroData = [
            'id' => 'test',
            'price' => '0.00',
            'size' => '0.00',
            'filledSize' => '0.00',
        ];

        $this->fakeHttp->addJsonResponse('GET', '/orders/test', $zeroData);

        $result = $this->client->orders()->get('test');

        expect($result['price'])->toBe('0.00')
            ->and($result['size'])->toBe('0.00')
            ->and($result['filledSize'])->toBe('0.00');
    });

    it('handles very large numbers', function () {
        $largeNumbers = [
            'id' => 'test',
            'volume' => '999999999999999.99',
            'liquidity' => '888888888888888.88',
        ];

        $this->fakeHttp->addJsonResponse('GET', '/markets/test', $largeNumbers);

        $result = $this->client->markets()->get('test');

        expect($result['volume'])->toBe('999999999999999.99')
            ->and($result['liquidity'])->toBe('888888888888888.88');
    });
});
