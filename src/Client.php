<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp;

use Danielgnh\PolymarketPhp\Http\HttpClientInterface;

class Client
{
    private Config $config;

    private ?Gamma $gammaClient = null;

    private ?Clob $clobClient = null;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        ?string $apiKey = null,
        array $options = [],
        ?HttpClientInterface $gammaHttpClient = null,
        ?HttpClientInterface $clobHttpClient = null
    ) {
        $this->config = new Config($apiKey, $options);

        if ($gammaHttpClient !== null) {
            $this->gammaClient = new Gamma($this->config, $gammaHttpClient);
        }

        if ($clobHttpClient !== null) {
            $this->clobClient = new Clob($this->config, $clobHttpClient);
        }
    }

    public function gamma(): Gamma
    {
        return $this->gammaClient ?? new Gamma($this->config);
    }

    public function clob(): Clob
    {
        return $this->clobClient ?? new Clob($this->config);
    }
}
