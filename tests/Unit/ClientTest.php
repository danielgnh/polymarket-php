<?php

declare(strict_types=1);

use Danielgnh\PolymarketPhp\Client;
use Danielgnh\PolymarketPhp\Clob;
use Danielgnh\PolymarketPhp\Gamma;
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
        'gamma_base_url' => 'https://custom-gamma.api.com',
        'clob_base_url' => 'https://custom-clob.api.com',
        'timeout' => 60,
    ]);

    expect($client)->toBeInstanceOf(Client::class);
});

it('provides gamma client', function () {
    $client = new Client();
    $gamma = $client->gamma();

    expect($gamma)->toBeInstanceOf(Gamma::class);
});

it('provides clob client', function () {
    $client = new Client();
    $clob = $client->clob();

    expect($clob)->toBeInstanceOf(Clob::class);
});

it('caches gamma client instance', function () {
    $client = new Client();

    $gamma1 = $client->gamma();
    $gamma2 = $client->gamma();

    // Should return the same cached instance
    expect($gamma1)->toBe($gamma2);
});

it('caches clob client instance', function () {
    $client = new Client();

    $clob1 = $client->clob();
    $clob2 = $client->clob();

    // Should return the same cached instance
    expect($clob1)->toBe($clob2);
});

it('gamma client provides markets resource', function () {
    $client = new Client();
    $markets = $client->gamma()->markets();

    expect($markets)->toBeInstanceOf(Markets::class);
});

it('clob client provides orders resource', function () {
    $client = new Client();
    $orders = $client->clob()->orders();

    expect($orders)->toBeInstanceOf(Orders::class);
});

it('creates new resource instances on each call', function () {
    $client = new Client();

    $markets1 = $client->gamma()->markets();
    $markets2 = $client->gamma()->markets();

    // Each call to markets() creates a new instance
    expect($markets1)->not->toBe($markets2)
        ->and($markets1)->toBeInstanceOf(Markets::class)
        ->and($markets2)->toBeInstanceOf(Markets::class);
});
