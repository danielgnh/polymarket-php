<?php

declare(strict_types=1);

use Danielgnh\PolymarketPhp\Exceptions\JsonParseException;
use Danielgnh\PolymarketPhp\Http\Response;

it('creates a response with status code, headers and body', function () {
    $response = new Response(
        statusCode: 200,
        headers: ['Content-Type' => 'application/json'],
        body: '{"success": true}'
    );

    expect($response->statusCode())->toBe(200)
        ->and($response->headers())->toBe(['Content-Type' => 'application/json'])
        ->and($response->body())->toBe('{"success": true}');
});

it('decodes json response body', function () {
    $response = new Response(
        statusCode: 200,
        headers: [],
        body: '{"id": "123", "name": "Test Market"}'
    );

    $json = $response->json();

    expect($json)->toBe(['id' => '123', 'name' => 'Test Market']);
});

it('throws exception on invalid json', function () {
    $response = new Response(
        statusCode: 200,
        headers: [],
        body: 'invalid json'
    );

    $response->json();
})->throws(JsonParseException::class);

it('identifies successful responses', function () {
    $successResponse = new Response(200, [], '');
    $createdResponse = new Response(201, [], '');
    $errorResponse = new Response(404, [], '');
    $serverErrorResponse = new Response(500, [], '');

    expect($successResponse->isSuccessful())->toBeTrue()
        ->and($createdResponse->isSuccessful())->toBeTrue()
        ->and($errorResponse->isSuccessful())->toBeFalse()
        ->and($serverErrorResponse->isSuccessful())->toBeFalse();
});

it('retrieves specific header by name', function () {
    $response = new Response(
        statusCode: 200,
        headers: [
            'Content-Type' => 'application/json',
            'X-Rate-Limit' => '100',
        ],
        body: ''
    );

    expect($response->header('Content-Type'))->toBe('application/json')
        ->and($response->header('X-Rate-Limit'))->toBe('100')
        ->and($response->header('Non-Existent'))->toBeNull();
});

it('handles empty response body', function () {
    $response = new Response(204, [], '');

    expect($response->body())->toBe('')
        ->and($response->isSuccessful())->toBeTrue();
});
