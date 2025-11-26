<?php

declare(strict_types=1);

use Danielgnh\PolymarketPhp\Config;

it('creates config with default values', function () {
    $config = new Config();

    expect($config->apiKey)->toBeNull()
        ->and($config->baseUrl)->toBe('https://gamma-api.polymarket.com')
        ->and($config->timeout)->toBe(30)
        ->and($config->retries)->toBe(3)
        ->and($config->verifySSL)->toBeTrue();
});

it('creates config with api key', function () {
    $config = new Config('test-api-key');

    expect($config->apiKey)->toBe('test-api-key');
});

it('allows custom base url', function () {
    $config = new Config(null, ['base_url' => 'https://custom-api.example.com']);

    expect($config->baseUrl)->toBe('https://custom-api.example.com');
});

it('allows custom timeout', function () {
    $config = new Config(null, ['timeout' => 60]);

    expect($config->timeout)->toBe(60);
});

it('allows custom retries', function () {
    $config = new Config(null, ['retries' => 5]);

    expect($config->retries)->toBe(5);
});

it('allows disabling ssl verification', function () {
    $config = new Config(null, ['verify_ssl' => false]);

    expect($config->verifySSL)->toBeFalse();
});

it('accepts multiple options at once', function () {
    $config = new Config('my-key', [
        'base_url' => 'https://test.com',
        'timeout' => 45,
        'retries' => 2,
        'verify_ssl' => false,
    ]);

    expect($config->apiKey)->toBe('my-key')
        ->and($config->baseUrl)->toBe('https://test.com')
        ->and($config->timeout)->toBe(45)
        ->and($config->retries)->toBe(2)
        ->and($config->verifySSL)->toBeFalse();
});
