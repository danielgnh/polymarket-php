<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Tests;

use Danielgnh\PolymarketPhp\Client;
use Danielgnh\PolymarketPhp\Http\FakeGuzzleHttpClient;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public FakeGuzzleHttpClient $fakeHttp;

    public Client $client;

    /**
     * Load a fixture file and return its contents as an array.
     */
    protected function loadFixture(string $filename): array
    {
        $path = __DIR__ . '/Fixtures/' . $filename;

        if (!file_exists($path)) {
            throw new \RuntimeException("Fixture file not found: {$path}");
        }

        $contents = file_get_contents($path);
        $data = json_decode($contents, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("Invalid JSON in fixture: {$filename}");
        }

        return $data;
    }
}
