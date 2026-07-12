<?php

declare(strict_types=1);

namespace PolymarketPhp\Polymarket\Signing\TypedData;

/**
 * EIP-712 payload for CLOB L1 authentication.
 */
class ClobAuthPayload implements TypedDataInterface
{
    /**
     * @param string $address  The wallet address
     * @param int    $timestamp Unix timestamp
     * @param int    $nonce     Nonce value (usually 0)
     * @param int    $chainId   Chain ID (137 for Polygon mainnet, 80002 for Amoy testnet)
     */
    public function __construct(
        private readonly string $address,
        private readonly int $timestamp,
        private readonly int $nonce = 0,
        private readonly int $chainId = 137
    ) {}

    public function getDomain(): array
    {
        return [
            'name'    => 'ClobAuthDomain',
            'version' => '1',
            'chainId' => $this->chainId,
        ];
    }

    public function getDomainTypes(): array
    {
        return [
            ['name' => 'name',    'type' => 'string'],
            ['name' => 'version', 'type' => 'string'],
            ['name' => 'chainId', 'type' => 'uint256'],
        ];
    }

    public function getPrimaryType(): string
    {
        return 'ClobAuth';
    }

    public function getTypes(): array
    {
        return [
            'ClobAuth' => [
                ['name' => 'address',   'type' => 'address'],
                ['name' => 'timestamp', 'type' => 'string'],
                ['name' => 'nonce',     'type' => 'uint256'],
                ['name' => 'message',   'type' => 'string'],
            ],
        ];
    }

    public function getMessage(): array
    {
        return [
            'address'   => strtolower($this->address),
            'timestamp' => (string) $this->timestamp,
            'nonce'     => $this->nonce,
            'message'   => 'This message attests that I control the given wallet',
        ];
    }
}
