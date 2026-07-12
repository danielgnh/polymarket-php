<?php

declare(strict_types=1);

use PolymarketPhp\Polymarket\Signing\TypedData\ClobAuthPayload;

describe('ClobAuthPayload::getPrimaryType()', function (): void {
    it('returns ClobAuth', function (): void {
        $payload = new ClobAuthPayload('0xAddress', 1_234_567_890);

        expect($payload->getPrimaryType())->toBe('ClobAuth');
    });
});

describe('ClobAuthPayload::getDomain()', function (): void {
    it('builds the correct domain structure', function (): void {
        $payload = new ClobAuthPayload('0xAddress', 1_234_567_890, 0, 137);

        expect($payload->getDomain())->toBe([
            'name'    => 'ClobAuthDomain',
            'version' => '1',
            'chainId' => 137,
        ]);
    });

    it('embeds the provided chain ID in the domain', function (): void {
        $payload = new ClobAuthPayload('0xAddress', 1_234_567_890, 0, 80002);

        expect($payload->getDomain()['chainId'])->toBe(80002);
    });

    it('defaults chain ID to 137 (Polygon mainnet)', function (): void {
        $payload = new ClobAuthPayload('0xAddress', 1_234_567_890);

        expect($payload->getDomain()['chainId'])->toBe(137);
    });
});

describe('ClobAuthPayload::getDomainTypes()', function (): void {
    it('contains name, version, and chainId fields', function (): void {
        $payload = new ClobAuthPayload('0xAddress', 1_234_567_890);

        $names = array_column($payload->getDomainTypes(), 'name');

        expect($names)->toBe(['name', 'version', 'chainId']);
    });
});

describe('ClobAuthPayload::getTypes()', function (): void {
    it('contains ClobAuth key with four fields', function (): void {
        $payload = new ClobAuthPayload('0xAddress', 1_234_567_890);
        $types = $payload->getTypes();

        expect($types)->toHaveKey('ClobAuth')
            ->and($types['ClobAuth'])->toHaveCount(4);
    });

    it('defines the correct field names and EIP-712 types', function (): void {
        $payload = new ClobAuthPayload('0xAddress', 1_234_567_890);
        $fields = $payload->getTypes()['ClobAuth'];

        expect($fields)->toBe([
            ['name' => 'address',   'type' => 'address'],
            ['name' => 'timestamp', 'type' => 'string'],
            ['name' => 'nonce',     'type' => 'uint256'],
            ['name' => 'message',   'type' => 'string'],
        ]);
    });
});

describe('ClobAuthPayload::getMessage()', function (): void {
    it('lowercases the wallet address', function (): void {
        $payload = new ClobAuthPayload('0xABCDEF1234567890ABCDEF1234567890ABCDEF12', 1_234_567_890);

        expect($payload->getMessage()['address'])
            ->toBe('0xabcdef1234567890abcdef1234567890abcdef12');
    });

    it('converts the timestamp to a string', function (): void {
        $payload = new ClobAuthPayload('0xAddress', 1_234_567_890);

        expect($payload->getMessage()['timestamp'])
            ->toBe('1234567890')
            ->toBeString();
    });

    it('keeps the nonce as an integer', function (): void {
        $payload = new ClobAuthPayload('0xAddress', 1_234_567_890, 5);

        expect($payload->getMessage()['nonce'])->toBe(5)->toBeInt();
    });

    it('defaults nonce to 0', function (): void {
        $payload = new ClobAuthPayload('0xAddress', 1_234_567_890);

        expect($payload->getMessage()['nonce'])->toBe(0);
    });

    it('includes the standard attestation message', function (): void {
        $payload = new ClobAuthPayload('0xAddress', 1_234_567_890);

        expect($payload->getMessage()['message'])
            ->toBe('This message attests that I control the given wallet');
    });
});
