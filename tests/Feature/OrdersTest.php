<?php

declare(strict_types=1);

use Danielgnh\PolymarketPhp\Client;
use Danielgnh\PolymarketPhp\Http\FakeGuzzleHttpClient;

beforeEach(function () {
    $this->fakeHttp = new FakeGuzzleHttpClient();
    $this->client = new Client(httpClient: $this->fakeHttp);
});

describe('Orders::list()', function () {
    it('fetches list of orders successfully', function () {
        $ordersData = $this->loadFixture('orders_list.json');

        $this->fakeHttp->addJsonResponse('GET', '/orders', $ordersData);

        $result = $this->client->orders()->list();

        expect($result)->toBeArray()
            ->and($result)->toHaveCount(2)
            ->and($result[0])->toHaveKey('id')
            ->and($result[0])->toHaveKey('marketId')
            ->and($result[0]['id'])->toBe('order_123456');
    });

    it('applies pagination parameters', function () {
        $ordersData = $this->loadFixture('orders_list.json');

        $this->fakeHttp->addJsonResponse('GET', '/orders', array_slice($ordersData, 0, 1));

        $result = $this->client->orders()->list(limit: 1, offset: 0);

        expect($result)->toHaveCount(1)
            ->and($result[0]['id'])->toBe('order_123456');
    });

    it('filters orders by status', function () {
        $ordersData = $this->loadFixture('orders_list.json');
        $openOrders = array_filter($ordersData, fn ($o) => $o['status'] === 'open');

        $this->fakeHttp->addJsonResponse('GET', '/orders', array_values($openOrders));

        $result = $this->client->orders()->list(filters: ['status' => 'open']);

        expect($result)->toBeArray();

        foreach ($result as $order) {
            expect($order['status'])->toBe('open');
        }
    });

    it('filters orders by market id', function () {
        $ordersData = $this->loadFixture('orders_list.json');
        $marketId = '0x1234567890abcdef';
        $marketOrders = array_filter($ordersData, fn ($o) => $o['marketId'] === $marketId);

        $this->fakeHttp->addJsonResponse('GET', '/orders', array_values($marketOrders));

        $result = $this->client->orders()->list(filters: ['marketId' => $marketId]);

        expect($result)->toBeArray();

        foreach ($result as $order) {
            expect($order['marketId'])->toBe($marketId);
        }
    });

    it('handles empty orders list', function () {
        $this->fakeHttp->addJsonResponse('GET', '/orders', []);

        $result = $this->client->orders()->list();

        expect($result)->toBeArray()
            ->and($result)->toBeEmpty();
    });
});

describe('Orders::get()', function () {
    it('fetches single order by id', function () {
        $orderData = $this->loadFixture('order.json');

        $this->fakeHttp->addJsonResponse('GET', '/orders/order_123456', $orderData);

        $result = $this->client->orders()->get('order_123456');

        expect($result)->toBeArray()
            ->and($result['id'])->toBe('order_123456')
            ->and($result['marketId'])->toBe('0x1234567890abcdef')
            ->and($result['status'])->toBe('open');
    });

    it('includes all order details', function () {
        $orderData = $this->loadFixture('order.json');

        $this->fakeHttp->addJsonResponse('GET', '/orders/order_123456', $orderData);

        $result = $this->client->orders()->get('order_123456');

        expect($result)->toHaveKeys([
            'id',
            'marketId',
            'outcome',
            'side',
            'price',
            'size',
            'filledSize',
            'status',
            'type',
            'createdAt',
            'updatedAt',
            'expiresAt',
        ]);
    });

    it('preserves decimal precision in order prices and sizes', function () {
        $orderData = $this->loadFixture('order.json');

        $this->fakeHttp->addJsonResponse('GET', '/orders/order_123456', $orderData);

        $result = $this->client->orders()->get('order_123456');

        // Verify decimal values are strings
        expect($result['price'])->toBeString()
            ->and($result['price'])->toBe('0.52')
            ->and($result['size'])->toBeString()
            ->and($result['size'])->toBe('100.00');
    });
});

describe('Orders::create()', function () {
    it('creates a new order successfully', function () {
        $createdOrder = $this->loadFixture('order_created.json');

        $this->fakeHttp->addJsonResponse('POST', '/orders', $createdOrder, 201);

        $orderData = [
            'marketId' => '0x1234567890abcdef',
            'outcome' => 'Yes',
            'side' => 'buy',
            'price' => '0.55',
            'size' => '50.00',
        ];

        $result = $this->client->orders()->create($orderData);

        expect($result)->toBeArray()
            ->and($result['id'])->toBe('order_new_001')
            ->and($result['status'])->toBe('open')
            ->and($result['price'])->toBe('0.55')
            ->and($result['size'])->toBe('50.00');
    });

    it('creates buy order', function () {
        $createdOrder = $this->loadFixture('order_created.json');

        $this->fakeHttp->addJsonResponse('POST', '/orders', $createdOrder, 201);

        $orderData = [
            'marketId' => '0x1234567890abcdef',
            'outcome' => 'Yes',
            'side' => 'buy',
            'price' => '0.55',
            'size' => '50.00',
        ];

        $result = $this->client->orders()->create($orderData);

        expect($result['side'])->toBe('buy');
    });

    it('creates sell order', function () {
        $orderData = [
            'marketId' => '0x1234567890abcdef',
            'outcome' => 'No',
            'side' => 'sell',
            'price' => '0.45',
            'size' => '25.00',
        ];

        $createdOrder = array_merge($this->loadFixture('order_created.json'), [
            'side' => 'sell',
            'price' => '0.45',
        ]);

        $this->fakeHttp->addJsonResponse('POST', '/orders', $createdOrder, 201);

        $result = $this->client->orders()->create($orderData);

        expect($result['side'])->toBe('sell');
    });

    it('returns newly created order with all fields', function () {
        $createdOrder = $this->loadFixture('order_created.json');

        $this->fakeHttp->addJsonResponse('POST', '/orders', $createdOrder, 201);

        $result = $this->client->orders()->create([
            'marketId' => '0x1234567890abcdef',
            'outcome' => 'Yes',
            'side' => 'buy',
            'price' => '0.55',
            'size' => '50.00',
        ]);

        expect($result)->toHaveKey('id')
            ->and($result)->toHaveKey('createdAt')
            ->and($result)->toHaveKey('status')
            ->and($result['status'])->toBe('open');
    });
});

describe('Orders::cancel()', function () {
    it('cancels an order successfully', function () {
        $cancelledOrder = $this->loadFixture('order_cancelled.json');

        $this->fakeHttp->addJsonResponse('DELETE', '/orders/order_123456', $cancelledOrder);

        $result = $this->client->orders()->cancel('order_123456');

        expect($result)->toBeArray()
            ->and($result['id'])->toBe('order_123456')
            ->and($result['status'])->toBe('cancelled')
            ->and($result)->toHaveKey('cancelledAt');
    });

    it('returns cancelled order details', function () {
        $cancelledOrder = $this->loadFixture('order_cancelled.json');

        $this->fakeHttp->addJsonResponse('DELETE', '/orders/order_123456', $cancelledOrder);

        $result = $this->client->orders()->cancel('order_123456');

        expect($result)->toHaveKeys([
            'id',
            'marketId',
            'status',
            'cancelledAt',
        ]);
    });
});

describe('Orders integration scenarios', function () {
    it('can create and then fetch order', function () {
        // Create order
        $createdOrder = $this->loadFixture('order_created.json');
        $this->fakeHttp->addJsonResponse('POST', '/orders', $createdOrder, 201);

        $orderData = [
            'marketId' => '0x1234567890abcdef',
            'outcome' => 'Yes',
            'side' => 'buy',
            'price' => '0.55',
            'size' => '50.00',
        ];

        $created = $this->client->orders()->create($orderData);
        $orderId = $created['id'];

        expect($orderId)->toBe('order_new_001');

        // Fetch the created order
        $orderDetails = $this->loadFixture('order_created.json');
        $this->fakeHttp->addJsonResponse('GET', "/orders/{$orderId}", $orderDetails);

        $fetched = $this->client->orders()->get($orderId);

        expect($fetched['id'])->toBe($orderId)
            ->and($fetched['status'])->toBe('open');
    });

    it('can create and then cancel order', function () {
        // Create order
        $createdOrder = $this->loadFixture('order_created.json');
        $this->fakeHttp->addJsonResponse('POST', '/orders', $createdOrder, 201);

        $created = $this->client->orders()->create([
            'marketId' => '0x1234567890abcdef',
            'outcome' => 'Yes',
            'side' => 'buy',
            'price' => '0.55',
            'size' => '50.00',
        ]);

        $orderId = $created['id'];

        // Cancel the order
        $cancelledOrder = array_merge($createdOrder, [
            'status' => 'cancelled',
            'cancelledAt' => '2025-01-15T13:00:00Z',
        ]);
        $this->fakeHttp->addJsonResponse('DELETE', "/orders/{$orderId}", $cancelledOrder);

        $cancelled = $this->client->orders()->cancel($orderId);

        expect($cancelled['id'])->toBe($orderId)
            ->and($cancelled['status'])->toBe('cancelled')
            ->and($cancelled)->toHaveKey('cancelledAt');
    });

    it('can list orders for specific market', function () {
        $marketId = '0x1234567890abcdef';
        $ordersData = $this->loadFixture('orders_list.json');
        $marketOrders = array_filter($ordersData, fn ($o) => $o['marketId'] === $marketId);

        $this->fakeHttp->addJsonResponse('GET', '/orders', array_values($marketOrders));

        $orders = $this->client->orders()->list(filters: ['marketId' => $marketId]);

        expect($orders)->toBeArray();

        foreach ($orders as $order) {
            expect($order['marketId'])->toBe($marketId);
        }
    });
});
