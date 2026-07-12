<?php

declare(strict_types=1);

namespace PolymarketPhp\Polymarket;

use PolymarketPhp\Polymarket\Auth\ClobAuthenticator;
use PolymarketPhp\Polymarket\Exceptions\ClobAuthenticationException;
use PolymarketPhp\Polymarket\Exceptions\SigningException;
use PolymarketPhp\Polymarket\Http\AsyncClient;
use PolymarketPhp\Polymarket\Http\AsyncClientInterface;
use PolymarketPhp\Polymarket\Http\GuzzleHttpClient;
use PolymarketPhp\Polymarket\Http\HttpClientInterface;
use PolymarketPhp\Polymarket\Signing\Eip712Signer;

class Client
{
    private readonly Config $config;

    private ?Gamma $gammaClient = null;

    private ?Clob $clobClient = null;

    private ?Bridge $bridgeClient = null;

    private ?ClobAuthenticator $clobAuthenticator = null;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        ?string $apiKey = null,
        array $options = [],
        ?HttpClientInterface $gammaHttpClient = null,
        ?HttpClientInterface $clobHttpClient = null,
        ?HttpClientInterface $bridgeHttpClient = null,
        ?AsyncClientInterface $gammaAsyncClient = null,
        ?AsyncClientInterface $clobAsyncClient = null,
    ) {
        $this->config = new Config($apiKey, $options);

        if ($gammaHttpClient instanceof HttpClientInterface) {
            $this->gammaClient = new Gamma($this->config, $gammaHttpClient, $gammaAsyncClient);
        }

        if ($clobHttpClient instanceof HttpClientInterface) {
            $this->clobClient = new Clob($this->config, $clobHttpClient, null, $clobAsyncClient);
        }

        if ($bridgeHttpClient instanceof HttpClientInterface) {
            $this->bridgeClient = new Bridge($this->config, $bridgeHttpClient);
        }
    }

    /**
     * Setup CLOB authentication using private key.
     *
     * This must be called before CLOB write operations.
     *
     * @param string|null $privateKey Hex private key (0x...).
     * @param int         $nonce Nonce for credential derivation (default: 0)
     *
     * @throws ClobAuthenticationException
     * @throws SigningException
     */
    public function auth(
        ?string $privateKey = null,
        int $nonce = 0
    ): void {
        $key = $privateKey ?? $this->config->privateKey;

        if ($key === null) {
            throw ClobAuthenticationException::missingPrivateKey();
        }

        $signer = new Eip712Signer($key, $this->config->chainId);

        $this->clobAuthenticator = new ClobAuthenticator(
            $signer,
            $this->config->clobBaseUrl,
            $this->config->chainId
        );

        $credentials = $this->clobAuthenticator->deriveOrCreateCredentials($nonce);
        $this->clobAuthenticator = $this->clobAuthenticator->withCredentials($credentials);

        $this->clobClient?->auth($this->clobAuthenticator);
    }

    public function gamma(): Gamma
    {
        if (!$this->gammaClient instanceof Gamma) {
            $httpClient = new GuzzleHttpClient($this->config->gammaBaseUrl, $this->config);
            $asyncClient = new AsyncClient($httpClient->getGuzzleClient(), $this->config);
            $this->gammaClient = new Gamma($this->config, $httpClient, $asyncClient);
        }

        return $this->gammaClient;
    }

    public function clob(): Clob
    {
        if (!$this->clobClient instanceof Clob) {
            $httpClient = new GuzzleHttpClient(
                $this->config->clobBaseUrl,
                $this->config,
                $this->clobAuthenticator
            );
            $asyncClient = new AsyncClient($httpClient->getGuzzleClient(), $this->config);
            $this->clobClient = new Clob(
                $this->config,
                $httpClient,
                $this->clobAuthenticator,
                $asyncClient
            );
        }

        return $this->clobClient;
    }

    /**
     * Get Bridge API client for cross-chain deposits.
     */
    public function bridge(): Bridge
    {
        if (!$this->bridgeClient instanceof Bridge) {
            $this->bridgeClient = new Bridge($this->config);
        }

        return $this->bridgeClient;
    }
}
