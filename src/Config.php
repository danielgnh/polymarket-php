<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp;

readonly class Config
{
    public string $baseUrl;

    public ?string $apiKey;

    public int $timeout;

    public int $retries;

    public bool $verifySSL;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(?string $apiKey = null, array $options = [])
    {
        $this->apiKey = $apiKey;
        $this->baseUrl = $options['base_url'] ?? 'https://gamma-api.polymarket.com';
        $this->timeout = $options['timeout'] ?? 30;
        $this->retries = $options['retries'] ?? 3;
        $this->verifySSL = $options['verify_ssl'] ?? true;
    }
}
