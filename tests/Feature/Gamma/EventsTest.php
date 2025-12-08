<?php

declare(strict_types=1);

use Danielgnh\PolymarketPhp\Client;
use Danielgnh\PolymarketPhp\Http\FakeGuzzleHttpClient;

beforeEach(function () {
    $this->fakeHttp = new FakeGuzzleHttpClient();
    $this->client = new Client(gammaHttpClient: $this->fakeHttp, clobHttpClient: $this->fakeHttp);
});

describe('Events::list()', function () {
    it('lists events with pagination', function () {
        $eventsData = [
            [
                'id' => 'event1',
                'title' => 'US Presidential Election 2024',
                'slug' => 'us-election-2024',
                'active' => true,
            ],
            [
                'id' => 'event2',
                'title' => 'Bitcoin Price Predictions',
                'slug' => 'bitcoin-predictions',
                'active' => true,
            ],
        ];

        $this->fakeHttp->addJsonResponse('GET', '/events', $eventsData);

        $result = $this->client->gamma()->events()->list();

        expect($result)->toBeArray()
            ->and($result)->toHaveCount(2)
            ->and($result[0]['title'])->toBe('US Presidential Election 2024');
    });

    it('applies limit parameter', function () {
        $eventsData = [
            ['id' => 'event1', 'title' => 'Event 1'],
        ];

        $this->fakeHttp->addJsonResponse('GET', '/events', $eventsData);

        $result = $this->client->gamma()->events()->list(limit: 1);

        expect($result)->toHaveCount(1);
    });

    it('applies offset parameter', function () {
        $eventsData = [
            ['id' => 'event2', 'title' => 'Event 2'],
        ];

        $this->fakeHttp->addJsonResponse('GET', '/events', $eventsData);

        $result = $this->client->gamma()->events()->list(offset: 1);

        expect($result)->toBeArray();
    });

    it('applies custom filters', function () {
        $eventsData = [
            ['id' => 'event1', 'title' => 'Active Event', 'active' => true],
        ];

        $this->fakeHttp->addJsonResponse('GET', '/events', $eventsData);

        $result = $this->client->gamma()->events()->list(filters: ['active' => true]);

        expect($result)->toBeArray()
            ->and($result[0]['active'])->toBeTrue();
    });
});

describe('Events::get()', function () {
    it('gets event by ID', function () {
        $eventData = [
            'id' => 'event1',
            'title' => 'US Presidential Election 2024',
            'description' => 'Who will win the 2024 US Presidential Election?',
            'slug' => 'us-election-2024',
            'active' => true,
            'markets' => ['market1', 'market2'],
        ];

        $this->fakeHttp->addJsonResponse('GET', '/events/event1', $eventData);

        $result = $this->client->gamma()->events()->get('event1');

        expect($result)->toBeArray()
            ->and($result['id'])->toBe('event1')
            ->and($result['title'])->toBe('US Presidential Election 2024')
            ->and($result['markets'])->toBeArray();
    });
});

describe('Events::getBySlug()', function () {
    it('gets event by slug', function () {
        $eventData = [
            'id' => 'event1',
            'title' => 'US Presidential Election 2024',
            'slug' => 'us-election-2024',
        ];

        $this->fakeHttp->addJsonResponse('GET', '/events/slug/us-election-2024', $eventData);

        $result = $this->client->gamma()->events()->getBySlug('us-election-2024');

        expect($result)->toBeArray()
            ->and($result['slug'])->toBe('us-election-2024')
            ->and($result['id'])->toBe('event1');
    });
});

describe('Events::tags()', function () {
    it('gets event tags', function () {
        $tagsData = [
            ['id' => 'tag1', 'label' => 'Politics'],
            ['id' => 'tag2', 'label' => 'US'],
        ];

        $this->fakeHttp->addJsonResponse('GET', '/events/event1/tags', $tagsData);

        $result = $this->client->gamma()->events()->tags('event1');

        expect($result)->toBeArray()
            ->and($result)->toHaveCount(2)
            ->and($result[0]['label'])->toBe('Politics');
    });
});
