<?php

declare(strict_types=1);

use PolymarketPhp\Polymarket\Signing\TypedData\OrderPayloadV2;

/**
 * Minimal order data covering all 11 V2 EIP-712 Order struct fields.
 *
 * @return array<string, mixed>
 */
function sampleOrderDataV2(): array
{
    return [
        'salt'          => 479_249_096_354,
        'maker'         => '0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266',
        'signer'        => '0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266',
        'tokenId'       => '1234',
        'makerAmount'   => '100000000',
        'takerAmount'   => '50000000',
        'side'          => 0,
        'signatureType' => 0,
        'timestamp'     => '1780449126930',
        'metadata'      => OrderPayloadV2::BYTES32_ZERO,
        'builder'       => OrderPayloadV2::BYTES32_ZERO,
    ];
}

describe('OrderPayloadV2::getPrimaryType()', function (): void {
    it('returns Order', function (): void {
        $payload = new OrderPayloadV2(sampleOrderDataV2());

        expect($payload->getPrimaryType())->toBe('Order');
    });
});

describe('OrderPayloadV2::getDomain() – version 2', function (): void {
    it('uses the Polymarket CTF Exchange name and version 2', function (): void {
        $payload = new OrderPayloadV2(sampleOrderDataV2());
        $domain = $payload->getDomain();

        expect($domain['name'])->toBe('Polymarket CTF Exchange')
            ->and($domain['version'])->toBe('2');
    });

    it('defaults to mainnet chain ID and the V2 exchange contract', function (): void {
        $payload = new OrderPayloadV2(sampleOrderDataV2());
        $domain = $payload->getDomain();

        expect($domain['chainId'])->toBe(OrderPayloadV2::MAINNET_CHAIN_ID)
            ->and($domain['verifyingContract'])->toBe(OrderPayloadV2::CTF_EXCHANGE_V2);
    });

    it('selects the neg-risk V2 exchange when negRisk is true', function (): void {
        $payload = new OrderPayloadV2(sampleOrderDataV2(), negRisk: true);

        expect($payload->getDomain()['verifyingContract'])
            ->toBe(OrderPayloadV2::NEG_RISK_CTF_EXCHANGE_V2);
    });

    it('uses the same V2 exchange contract on testnet', function (): void {
        $payload = new OrderPayloadV2(sampleOrderDataV2(), OrderPayloadV2::TESTNET_CHAIN_ID);
        $domain = $payload->getDomain();

        expect($domain['chainId'])->toBe(OrderPayloadV2::TESTNET_CHAIN_ID)
            ->and($domain['verifyingContract'])->toBe(OrderPayloadV2::CTF_EXCHANGE_V2);
    });
});

describe('OrderPayloadV2::getDomain() – version 3', function (): void {
    it('uses domain version 3 and the mainnet V3 exchange contract', function (): void {
        $payload = new OrderPayloadV2(sampleOrderDataV2(), version: 3);
        $domain = $payload->getDomain();

        expect($domain['version'])->toBe('3')
            ->and($domain['verifyingContract'])->toBe(OrderPayloadV2::CTF_EXCHANGE_V3_MAINNET);
    });

    it('selects the testnet V3 exchange contract on Amoy', function (): void {
        $payload = new OrderPayloadV2(
            sampleOrderDataV2(),
            OrderPayloadV2::TESTNET_CHAIN_ID,
            version: 3,
        );

        expect($payload->getDomain()['verifyingContract'])
            ->toBe(OrderPayloadV2::CTF_EXCHANGE_V3_TESTNET);
    });

    it('ignores the negRisk flag on version 3 (single exchange contract)', function (): void {
        $payload = new OrderPayloadV2(sampleOrderDataV2(), negRisk: true, version: 3);

        expect($payload->getDomain()['verifyingContract'])
            ->toBe(OrderPayloadV2::CTF_EXCHANGE_V3_MAINNET);
    });
});

describe('OrderPayloadV2 – validation and overrides', function (): void {
    it('rejects unsupported order versions', function (): void {
        expect(fn (): OrderPayloadV2 => new OrderPayloadV2(sampleOrderDataV2(), version: 1))
            ->toThrow(InvalidArgumentException::class, 'version');
    });

    it('accepts an explicit verifying contract address overriding auto-selection', function (): void {
        $custom = '0x1234567890AbcDef1234567890AbCdef12345678';
        $payload = new OrderPayloadV2(
            sampleOrderDataV2(),
            verifyingContract: $custom,
        );

        expect($payload->getDomain()['verifyingContract'])->toBe($custom);
    });
});

describe('OrderPayloadV2::getTypes()', function (): void {
    it('exposes exactly the 11 V2 Order struct fields in canonical order', function (): void {
        $payload = new OrderPayloadV2(sampleOrderDataV2());
        $fieldNames = array_column($payload->getTypes()['Order'], 'name');

        expect($fieldNames)->toBe([
            'salt',
            'maker',
            'signer',
            'tokenId',
            'makerAmount',
            'takerAmount',
            'side',
            'signatureType',
            'timestamp',
            'metadata',
            'builder',
        ]);
    });

    it('does not contain the removed V1 fields', function (): void {
        $payload = new OrderPayloadV2(sampleOrderDataV2());
        $fieldNames = array_column($payload->getTypes()['Order'], 'name');

        expect($fieldNames)->not->toContain('taker')
            ->and($fieldNames)->not->toContain('expiration')
            ->and($fieldNames)->not->toContain('nonce')
            ->and($fieldNames)->not->toContain('feeRateBps');
    });

    it('types metadata and builder as bytes32, timestamp as uint256', function (): void {
        $payload = new OrderPayloadV2(sampleOrderDataV2());
        $typesByName = array_column($payload->getTypes()['Order'], 'type', 'name');

        expect($typesByName['metadata'])->toBe('bytes32')
            ->and($typesByName['builder'])->toBe('bytes32')
            ->and($typesByName['timestamp'])->toBe('uint256');
    });
});

describe('OrderPayloadV2::getMessage()', function (): void {
    it('returns the order data as-is', function (): void {
        $data = sampleOrderDataV2();
        $payload = new OrderPayloadV2($data);

        expect($payload->getMessage())->toBe($data);
    });
});
