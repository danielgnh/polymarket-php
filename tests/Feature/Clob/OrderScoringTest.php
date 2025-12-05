<?php

declare(strict_types=1);

use Danielgnh\PolymarketPhp\Client;
use Danielgnh\PolymarketPhp\Http\FakeGuzzleHttpClient;

beforeEach(function () {
    $this->fakeHttp = new FakeGuzzleHttpClient();
    $this->client = new Client(clobHttpClient: $this->fakeHttp);
});

describe('OrderScoring::check()', function () {
    it('checks if order qualifies for scoring', function () {
        $scoringData = [
            'qualifies' => true,
            'score' => 95,
            'reason' => 'Order meets all criteria',
        ];

        $this->fakeHttp->addJsonResponse('GET', '/order-scoring', $scoringData);

        $result = $this->client->clob()->orderScoring()->check(['order_id' => 'order_123']);

        expect($result)->toBeArray()
            ->and($result['qualifies'])->toBeTrue()
            ->and($result['score'])->toBe(95);
    });

    it('handles non-qualifying orders', function () {
        $scoringData = [
            'qualifies' => false,
            'score' => 0,
            'reason' => 'Order size too small',
        ];

        $this->fakeHttp->addJsonResponse('GET', '/order-scoring', $scoringData);

        $result = $this->client->clob()->orderScoring()->check(['order_id' => 'order_456']);

        expect($result)->toBeArray()
            ->and($result['qualifies'])->toBeFalse()
            ->and($result['reason'])->toContain('too small');
    });
});

describe('OrderScoring::checkMultiple()', function () {
    it('checks multiple orders for scoring eligibility', function () {
        $multipleScoresData = [
            ['order_id' => 'order_1', 'qualifies' => true, 'score' => 90],
            ['order_id' => 'order_2', 'qualifies' => false, 'score' => 0],
            ['order_id' => 'order_3', 'qualifies' => true, 'score' => 85],
        ];

        $this->fakeHttp->addJsonResponse('POST', '/orders-scoring', $multipleScoresData);

        $result = $this->client->clob()->orderScoring()->checkMultiple([
            ['order_id' => 'order_1'],
            ['order_id' => 'order_2'],
            ['order_id' => 'order_3'],
        ]);

        expect($result)->toBeArray()
            ->and($result)->toHaveCount(3)
            ->and($result[0]['qualifies'])->toBeTrue()
            ->and($result[1]['qualifies'])->toBeFalse();
    });
});
