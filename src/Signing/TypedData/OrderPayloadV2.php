<?php

declare(strict_types=1);

namespace PolymarketPhp\Polymarket\Signing\TypedData;

use InvalidArgumentException;

/**
 * EIP-712 payload for Polymarket CTF Exchange V2/V3 order signing.
 *
 * CLOB V2 went live on 2026-04-28: the Order struct dropped `taker`,
 * `expiration`, `nonce`, and `feeRateBps`, gained `timestamp`, `metadata`,
 * and `builder`, and the domain version now matches the order version
 * ("2" or "3"). The verifying contract is auto-derived from the chain ID,
 * order version, and neg-risk flag unless overridden explicitly.
 */
class OrderPayloadV2 implements TypedDataInterface
{
    public const MAINNET_CHAIN_ID = 137;

    public const TESTNET_CHAIN_ID = 80002;

    /** Exchange V2 contracts are deployed at the same address on Polygon and Amoy. */
    public const CTF_EXCHANGE_V2 = '0xE111180000d2663C0091e4f400237545B87B996B';

    public const NEG_RISK_CTF_EXCHANGE_V2 = '0xe2222d279d744050d28e00520010520000310F59';

    /** Exchange V3 has no separate neg-risk deployment. */
    public const CTF_EXCHANGE_V3_MAINNET = '0xe3333700cA9d93003F00f0F71f8515005F6c00Aa';

    public const CTF_EXCHANGE_V3_TESTNET = '0x9fE6e61422AdB6F610d8597F9684b16912D50C3D';

    public const BYTES32_ZERO = '0x0000000000000000000000000000000000000000000000000000000000000000';

    private readonly string $verifyingContract;

    /**
     * @param array<string, mixed> $orderData
     * @param int                  $chainId           Chain ID – drives contract auto-selection
     * @param bool                 $negRisk           Sign against the neg-risk exchange (version 2 only)
     * @param int                  $version           Order version (2 or 3), also the domain version
     * @param string|null          $verifyingContract Explicit override; derived when null
     */
    public function __construct(
        private readonly array $orderData,
        private readonly int $chainId = self::MAINNET_CHAIN_ID,
        private readonly bool $negRisk = false,
        private readonly int $version = 2,
        ?string $verifyingContract = null,
    ) {
        if (!in_array($this->version, [2, 3], true)) {
            throw new InvalidArgumentException(
                "Unsupported order version {$this->version}; supported versions are 2 and 3."
            );
        }

        $this->verifyingContract = $verifyingContract ?? $this->deriveContract();
    }

    public function getDomainTypes(): array
    {
        return [
            ['name' => 'name',              'type' => 'string'],
            ['name' => 'version',           'type' => 'string'],
            ['name' => 'chainId',           'type' => 'uint256'],
            ['name' => 'verifyingContract', 'type' => 'address'],
        ];
    }

    public function getDomain(): array
    {
        return [
            'name'              => 'Polymarket CTF Exchange',
            'version'           => (string) $this->version,
            'chainId'           => $this->chainId,
            'verifyingContract' => $this->verifyingContract,
        ];
    }

    public function getPrimaryType(): string
    {
        return 'Order';
    }

    public function getTypes(): array
    {
        return [
            'Order' => [
                ['name' => 'salt',          'type' => 'uint256'],
                ['name' => 'maker',         'type' => 'address'],
                ['name' => 'signer',        'type' => 'address'],
                ['name' => 'tokenId',       'type' => 'uint256'],
                ['name' => 'makerAmount',   'type' => 'uint256'],
                ['name' => 'takerAmount',   'type' => 'uint256'],
                ['name' => 'side',          'type' => 'uint8'],
                ['name' => 'signatureType', 'type' => 'uint8'],
                ['name' => 'timestamp',     'type' => 'uint256'],
                ['name' => 'metadata',      'type' => 'bytes32'],
                ['name' => 'builder',       'type' => 'bytes32'],
            ],
        ];
    }

    public function getMessage(): array
    {
        return $this->orderData;
    }

    private function deriveContract(): string
    {
        if ($this->version === 3) {
            return $this->chainId === self::TESTNET_CHAIN_ID
                ? self::CTF_EXCHANGE_V3_TESTNET
                : self::CTF_EXCHANGE_V3_MAINNET;
        }

        return $this->negRisk
            ? self::NEG_RISK_CTF_EXCHANGE_V2
            : self::CTF_EXCHANGE_V2;
    }
}
