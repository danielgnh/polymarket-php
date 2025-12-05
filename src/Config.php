<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp;

class Config
{
    public readonly string $gammaBaseUrl;

    public readonly string $clobBaseUrl;

    public readonly ?string $apiKey;

    public readonly int $timeout;

    public readonly int $retries;

    public readonly bool $verifySSL;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(?string $apiKey = null, array $options = [])
    {
        $this->apiKey = $apiKey;
        $this->gammaBaseUrl = $options['gamma_base_url'] ?? 'https://gamma-api.polymarket.com';
        $this->clobBaseUrl = $options['clob_base_url'] ?? 'https://clob.polymarket.com';
        $this->timeout = $options['timeout'] ?? 30;
        $this->retries = $options['retries'] ?? 3;
        $this->verifySSL = $options['verify_ssl'] ?? true;
    }
}
