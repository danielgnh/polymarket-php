<?php

declare(strict_types=1);

namespace PolymarketPhp\Polymarket\Auth;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use PolymarketPhp\Polymarket\Auth\Signer\HmacSigner;
use PolymarketPhp\Polymarket\Exceptions\ClobAuthenticationException;
use PolymarketPhp\Polymarket\Signing\Eip712Signer;
use PolymarketPhp\Polymarket\Signing\TypedData\ClobAuthPayload;

/**
 * Manages CLOB authentication flow (L1 and L2).
 *
 * Responsibilities:
 * - Coordinate credential derivation/creation
 * - Store API credentials securely
 * - Provide header generation for requests
 */
class ClobAuthenticator
{
    public function __construct(
        private readonly Eip712Signer $signer,
        private readonly string $clobBaseUrl,
        private readonly int $chainId = 137,
        private readonly ?ApiCredentials $credentials = null
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

            /** @var array<string, mixed> $data */
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

            /** @var array<string, mixed> $data */
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
        $timestamp ??= time();
        $signature = $this->signer->sign(
            new ClobAuthPayload($this->signer->getAddress(), $timestamp, $nonce, $this->chainId)
        );

        return [
            'POLY_ADDRESS' => strtolower($this->signer->getAddress()),
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
        if (!$this->credentials instanceof ApiCredentials) {
            throw ClobAuthenticationException::notSetup();
        }

        $timestamp ??= time();

        $signature = HmacSigner::sign(
            (string) $timestamp,
            strtoupper($method),
            $path,
            $body,
            $this->credentials->apiSecret
        );

        return [
            'POLY_ADDRESS' => $this->signer->getAddress(),
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
        return $this->credentials instanceof ApiCredentials;
    }

    /**
     * Get wallet address.
     */
    public function getAddress(): string
    {
        return $this->signer->getAddress();
    }

    /**
     * Expose the underlying signer so resources can sign payloads directly.
     */
    public function getSigner(): Eip712Signer
    {
        return $this->signer;
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
