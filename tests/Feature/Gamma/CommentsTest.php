<?php

declare(strict_types=1);

use Danielgnh\PolymarketPhp\Client;
use Danielgnh\PolymarketPhp\Http\FakeGuzzleHttpClient;

beforeEach(function () {
    $this->fakeHttp = new FakeGuzzleHttpClient();
    $this->client = new Client(gammaHttpClient: $this->fakeHttp, clobHttpClient: $this->fakeHttp);
});

describe('Comments::list()', function () {
    it('lists comments', function () {
        $commentsData = [
            [
                'id' => 'comment1',
                'content' => 'I think Bitcoin will reach 100k',
                'author' => '0x1234...',
                'timestamp' => '2025-01-15T12:00:00Z',
            ],
            [
                'id' => 'comment2',
                'content' => 'Unlikely to happen this year',
                'author' => '0x5678...',
                'timestamp' => '2025-01-15T12:30:00Z',
            ],
        ];

        $this->fakeHttp->addJsonResponse('GET', '/comments', $commentsData);

        $result = $this->client->gamma()->comments()->list();

        expect($result)->toBeArray()
            ->and($result)->toHaveCount(2)
            ->and($result[0]['content'])->toBe('I think Bitcoin will reach 100k');
    });
});

describe('Comments::get()', function () {
    it('gets comment by ID', function () {
        $commentData = [
            'id' => 'comment1',
            'content' => 'I think Bitcoin will reach 100k',
            'author' => '0x1234...',
            'timestamp' => '2025-01-15T12:00:00Z',
            'marketId' => 'market1',
        ];

        $this->fakeHttp->addJsonResponse('GET', '/comments/comment1', $commentData);

        $result = $this->client->gamma()->comments()->get('comment1');

        expect($result)->toBeArray()
            ->and($result['id'])->toBe('comment1')
            ->and($result['content'])->toBe('I think Bitcoin will reach 100k')
            ->and($result['marketId'])->toBe('market1');
    });
});

describe('Comments::byUserAddress()', function () {
    it('gets comments by user address', function () {
        $commentsData = [
            [
                'id' => 'comment1',
                'content' => 'Comment 1',
                'author' => '0x1234567890abcdef',
            ],
            [
                'id' => 'comment2',
                'content' => 'Comment 2',
                'author' => '0x1234567890abcdef',
            ],
        ];

        $this->fakeHttp->addJsonResponse('GET', '/comments/user_address/0x1234567890abcdef', $commentsData);

        $result = $this->client->gamma()->comments()->byUserAddress('0x1234567890abcdef');

        expect($result)->toBeArray()
            ->and($result)->toHaveCount(2)
            ->and($result[0]['author'])->toBe('0x1234567890abcdef')
            ->and($result[1]['author'])->toBe('0x1234567890abcdef');
    });

    it('handles empty comments for user', function () {
        $this->fakeHttp->addJsonResponse('GET', '/comments/user_address/0xnonexistent', []);

        $result = $this->client->gamma()->comments()->byUserAddress('0xnonexistent');

        expect($result)->toBeArray()
            ->and($result)->toBeEmpty();
    });
});
