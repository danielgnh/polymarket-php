<?php

declare(strict_types=1);

use PolymarketPhp\Polymarket\Signing\TypedData\OrderPayload;

/**
 * Minimal order data covering all 12 EIP-712 Order struct fields.
 *
 * @return array<string, mixed>
 */
function sampleOrderData(): array
{
    return [
        'salt'          => 12_345,
        'maker'         => '0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266',
        'signer'        => '0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266',
        'taker'         => '0x0000000000000000000000000000000000000000',
        'tokenId'       => '71321045679252212594626385532706912750332728571942532289631379312455583992563',
        'makerAmount'   => '100000',
        'takerAmount'   => '100000',
        'expiration'    => 0,
        'nonce'         => 0,
        'feeRateBps'    => 0,
        'side'          => 0,
        'signatureType' => 0,
    ];
}

describe('OrderPayload::getPrimaryType()', function (): void {
    it('returns Order', function (): void {
        $payload = new OrderPayload(sampleOrderData());

        expect($payload->getPrimaryType())->toBe('Order');
    });
});

describe('OrderPayload::getDomain() – mainnet', function (): void {
    it('uses the Polymarket CTF Exchange name and version 1', function (): void {
        $payload = new OrderPayload(sampleOrderData());
        $domain = $payload->getDomain();

        expect($domain['name'])->toBe('Polymarket CTF Exchange')
            ->and($domain['version'])->toBe('1');
    });

    it('defaults to mainnet chain ID and mainnet verifying contract', function (): void {
        $payload = new OrderPayload(sampleOrderData());
        $domain = $payload->getDomain();

        expect($domain['chainId'])->toBe(OrderPayload::MAINNET_CHAIN_ID)
            ->and($domain['verifyingContract'])->toBe(OrderPayload::CTF_EXCHANGE_MAINNET);
    });
});

describe('OrderPayload::getDomain() – testnet', function (): void {
    it('auto-selects the testnet contract when chain ID is 80002', function (): void {
        $payload = new OrderPayload(sampleOrderData(), OrderPayload::TESTNET_CHAIN_ID);
        $domain = $payload->getDomain();

        expect($domain['chainId'])->toBe(OrderPayload::TESTNET_CHAIN_ID)
            ->and($domain['verifyingContract'])->toBe(OrderPayload::CTF_EXCHANGE_TESTNET);
    });

    it('mainnet chain ID always selects the mainnet contract', function (): void {
        $payload = new OrderPayload(sampleOrderData(), OrderPayload::MAINNET_CHAIN_ID);

        expect($payload->getDomain()['verifyingContract'])->toBe(OrderPayload::CTF_EXCHANGE_MAINNET);
    });
});

describe('OrderPayload – custom verifying contract', function (): void {
    it('accepts an explicit verifying contract address overriding auto-selection', function (): void {
        $custom = '0x1234567890AbcDef1234567890AbCdef12345678';
        $payload = new OrderPayload(sampleOrderData(), OrderPayload::MAINNET_CHAIN_ID, $custom);

        expect($payload->getDomain()['verifyingContract'])->toBe($custom);
    });
});

describe('OrderPayload::getDomainTypes()', function (): void {
    it('contains name, version, chainId, and verifyingContract', function (): void {
        $payload = new OrderPayload(sampleOrderData());
        $names = array_column($payload->getDomainTypes(), 'name');

        expect($names)->toBe(['name', 'version', 'chainId', 'verifyingContract']);
    });
});

describe('OrderPayload::getTypes()', function (): void {
    it('exposes all 12 EIP-712 Order struct fields', function (): void {
        $payload = new OrderPayload(sampleOrderData());
        $fieldNames = array_column($payload->getTypes()['Order'], 'name');

        expect($fieldNames)->toContain('salt')
            ->and($fieldNames)->toContain('maker')
            ->and($fieldNames)->toContain('signer')
            ->and($fieldNames)->toContain('taker')
            ->and($fieldNames)->toContain('tokenId')
            ->and($fieldNames)->toContain('makerAmount')
            ->and($fieldNames)->toContain('takerAmount')
            ->and($fieldNames)->toContain('expiration')
            ->and($fieldNames)->toContain('nonce')
            ->and($fieldNames)->toContain('feeRateBps')
            ->and($fieldNames)->toContain('side')
            ->and($fieldNames)->toContain('signatureType');
    });

    it('types side as uint8 and signatureType as uint8', function (): void {
        $payload = new OrderPayload(sampleOrderData());
        $typesByName = array_column($payload->getTypes()['Order'], 'type', 'name');

        expect($typesByName['side'])->toBe('uint8')
            ->and($typesByName['signatureType'])->toBe('uint8');
    });
});

describe('OrderPayload::getMessage()', function (): void {
    it('returns the order data as-is', function (): void {
        $data = sampleOrderData();
        $payload = new OrderPayload($data);

        expect($payload->getMessage())->toBe($data);
    });
});
