<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Auth;

use Danielgnh\PolymarketPhp\Auth\Signer\Eip712Signer;
use Danielgnh\PolymarketPhp\Auth\Signer\HmacSigner;
use Danielgnh\PolymarketPhp\Exceptions\ClobAuthenticationException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Manages CLOB authentication flow (L1 and L2).
 *
 * Responsibilities:
 * - Coordinate credential derivation/creation
 * - Store API credentials securely
 * - Provide header generation for requests
 */
readonly class ClobAuthenticator
{
    public function __construct(
        private Eip712Signer $signer,
        private string $clobBaseUrl,
        private int $chainId = 137,
        private ?ApiCredentials $credentials = null
    ) {}

    /**
     * Derive or create API credentials using L1 authentication.
     *
     * Tries to derive existing credentials first. If that fails, creates new ones.
     *
     * @throws ClobAuthenticationException
     */
    public function deriveOrCreateCredentials(int $nonce = 0): ApiCredentials
    {
        try {
            return $this->deriveApiKey($nonce);
        } catch (ClobAuthenticationException) {
            return $this->createApiKey($nonce);
        }
    }

    /**
     * Create new API credentials.
     *
     * @throws ClobAuthenticationException
     */
    public function createApiKey(int $nonce = 0): ApiCredentials
    {
        $headers = $this->generateL1Headers($nonce);

        try {
            $client = new GuzzleClient(['base_uri' => $this->clobBaseUrl]);
            $response = $client->post('/auth/api-key', [
                'headers' => $headers,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (!is_array($data)) {
                throw ClobAuthenticationException::credentialDerivationFailed(
                    'Invalid response from API'
                );
            }

            return ApiCredentials::fromArray($data);
        } catch (GuzzleException $e) {
            throw ClobAuthenticationException::credentialDerivationFailed($e->getMessage());
        }
    }

    /**
     * Derive existing API credentials.
     *
     * @throws ClobAuthenticationException
     */
    public function deriveApiKey(int $nonce = 0): ApiCredentials
    {
        $headers = $this->generateL1Headers($nonce);

        try {
            $client = new GuzzleClient(['base_uri' => $this->clobBaseUrl]);
            $response = $client->get('/auth/derive-api-key', [
                'headers' => $headers,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (!is_array($data)) {
                throw ClobAuthenticationException::credentialDerivationFailed(
                    'Invalid response from API'
                );
            }

            return ApiCredentials::fromArray($data);
        } catch (GuzzleException $e) {
            throw ClobAuthenticationException::credentialDerivationFailed($e->getMessage());
        }
    }

    /**
     * Generate L1 headers for credential operations.
     *
     * @return array<string, string>
     */
    public function generateL1Headers(int $nonce = 0, ?int $timestamp = null): array
    {
        $timestamp = $timestamp ?? time();
        $signature = $this->signer->signClobAuth($timestamp, $nonce);

        return [
            'POLY_ADDRESS' => strtolower($this->signer->address),
            'POLY_SIGNATURE' => $signature,
            'POLY_TIMESTAMP' => (string) $timestamp,
            'POLY_NONCE' => (string) $nonce,
        ];
    }

    /**
     * Generate L2 headers for API requests.
     *
     * @return array<string, string>
     *
     * @throws ClobAuthenticationException
     */
    public function generateL2Headers(
        string $method,
        string $path,
        ?string $body = null,
        ?int $timestamp = null
    ): array {
        if ($this->credentials === null) {
            throw ClobAuthenticationException::notSetup();
        }

        $timestamp = $timestamp ?? time();

        $signature = HmacSigner::sign(
            (string) $timestamp,
            strtoupper($method),
            $path,
            $body,
            $this->credentials->apiSecret
        );

        return [
            'POLY_ADDRESS' => $this->signer->address,
            'POLY_SIGNATURE' => $signature,
            'POLY_TIMESTAMP' => (string) $timestamp,
            'POLY_API_KEY' => $this->credentials->apiKey,
            'POLY_PASSPHRASE' => $this->credentials->passphrase,
        ];
    }

    /**
     * Check if L2 credentials are available.
     */
    public function hasCredentials(): bool
    {
        return $this->credentials !== null;
    }

    /**
     * Get wallet address.
     */
    public function getAddress(): string
    {
        return $this->signer->address;
    }

    /**
     * Create a new instance with credentials (for immutability).
     */
    public function withCredentials(ApiCredentials $credentials): self
    {
        return new self(
            $this->signer,
            $this->clobBaseUrl,
            $this->chainId,
            $credentials
        );
    }
}
