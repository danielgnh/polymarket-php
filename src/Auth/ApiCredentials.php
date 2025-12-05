<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Auth;

/**
 * Represents L2 API credentials returned from CLOB.
 */
readonly class ApiCredentials
{
    public function __construct(
        public string $apiKey,
        public string $apiSecret,
        public string $passphrase
    ) {}

    /**
     * Create from API response array.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $apiKey = $data['apiKey'] ?? $data['api_key'] ?? '';
        $apiSecret = $data['secret'] ?? $data['api_secret'] ?? '';
        $passphrase = $data['passphrase'] ?? '';

        return new self(
            apiKey: is_string($apiKey) ? $apiKey : '',
            apiSecret: is_string($apiSecret) ? $apiSecret : '',
            passphrase: is_string($passphrase) ? $passphrase : ''
        );
    }

    /**
     * Convert to array for storage/serialization.
     *
     * @return array{apiKey: string, secret: string, passphrase: string}
     */
    public function toArray(): array
    {
        return [
            'apiKey' => $this->apiKey,
            'secret' => $this->apiSecret,
            'passphrase' => $this->passphrase,
        ];
    }
}
