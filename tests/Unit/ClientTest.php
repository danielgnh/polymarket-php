<?php

declare(strict_types=1);

use Danielgnh\PolymarketPhp\Client;
use Danielgnh\PolymarketPhp\Resources\Markets;
use Danielgnh\PolymarketPhp\Resources\Orders;

it('creates client with default configuration', function () {
    $client = new Client();

    expect($client)->toBeInstanceOf(Client::class);
});

it('creates client with api key', function () {
    $client = new Client('test-api-key');

    expect($client)->toBeInstanceOf(Client::class);
});

it('creates client with custom options', function () {
    $client = new Client('test-key', [
        'base_url' => 'https://custom.api.com',
        'timeout' => 60,
    ]);

    expect($client)->toBeInstanceOf(Client::class);
});

it('provides markets resource', function () {
    $client = new Client();
    $markets = $client->markets();

    expect($markets)->toBeInstanceOf(Markets::class);
});

it('provides orders resource', function () {
    $client = new Client();
    $orders = $client->orders();

    expect($orders)->toBeInstanceOf(Orders::class);
});

it('creates new resource instances on each call', function () {
    $client = new Client();

    $markets1 = $client->markets();
    $markets2 = $client->markets();

    // Each call creates a new instance
    expect($markets1)->not->toBe($markets2)
        ->and($markets1)->toBeInstanceOf(Markets::class)
        ->and($markets2)->toBeInstanceOf(Markets::class);
});
