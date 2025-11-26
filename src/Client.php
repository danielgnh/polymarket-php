<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp;

use Danielgnh\PolymarketPhp\Http\GuzzleHttpClient;
use Danielgnh\PolymarketPhp\Http\HttpClientInterface;
use Danielgnh\PolymarketPhp\Resources\Markets;
use Danielgnh\PolymarketPhp\Resources\Orders;

class Client
{
    private HttpClientInterface $httpClient {
        get {
            return $this->httpClient;
        }
    }

    private Config $config {
        get {
            return $this->config;
        }
    }

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        ?string $apiKey = null,
        array $options = [],
        ?HttpClientInterface $httpClient = null
    ) {
        $this->config = new Config($apiKey, $options);
        $this->httpClient = $httpClient ?? new GuzzleHttpClient($this->config);
    }

    public function markets(): Markets
    {
        return new Markets($this->httpClient);
    }

    public function orders(): Orders
    {
        return new Orders($this->httpClient);
    }
}
