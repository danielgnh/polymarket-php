<?php

declare(strict_types=1);

use PolymarketPhp\Polymarket\Exceptions\ClobAuthenticationException;
use PolymarketPhp\Polymarket\Signing\Eip712Signer;
use PolymarketPhp\Polymarket\Signing\TypedData\ClobAuthPayload;

// Hardhat account #0 – a well-known test vector, safe to commit.
const EIP712_TEST_KEY = '0xac0974bec39a17e36ba4a6b4d238ff944bacb478cbed5efcae784d7bf4f2ff80';
const EIP712_TEST_ADDRESS = '0xf39fd6e51aad88f6f4ce6ab8827279cfffb92266';

describe('Eip712Signer::__construct()', function (): void {
    it('derives the correct Ethereum address from the private key', function (): void {
        $signer = new Eip712Signer(EIP712_TEST_KEY);

        expect(strtolower($signer->getAddress()))->toBe(EIP712_TEST_ADDRESS);
    });

    it('accepts a private key without the 0x prefix', function (): void {
        $keyWithout0x = substr(EIP712_TEST_KEY, 2);
        $signer = new Eip712Signer($keyWithout0x);

        expect(strtolower($signer->getAddress()))->toBe(EIP712_TEST_ADDRESS);
    });

    it('throws ClobAuthenticationException for a non-hex private key', function (): void {
        expect(fn (): Eip712Signer => new Eip712Signer('not-a-valid-private-key'))
            ->toThrow(ClobAuthenticationException::class);
    });

    it('throws ClobAuthenticationException for a key with wrong byte length', function (): void {
        // 31 bytes (62 hex chars) — one byte short
        expect(fn (): Eip712Signer => new Eip712Signer('0x' . str_repeat('ab', 31)))
            ->toThrow(ClobAuthenticationException::class);
    });
});

describe('Eip712Signer::getChainId()', function (): void {
    it('returns 137 (mainnet) by default', function (): void {
        $signer = new Eip712Signer(EIP712_TEST_KEY);

        expect($signer->getChainId())->toBe(137);
    });

    it('returns the chain ID provided at construction time', function (): void {
        $signer = new Eip712Signer(EIP712_TEST_KEY, 80002);

        expect($signer->getChainId())->toBe(80002);
    });
});

describe('Eip712Signer::sign()', function (): void {
    it('returns a valid 0x-prefixed 65-byte Ethereum signature', function (): void {
        $signer = new Eip712Signer(EIP712_TEST_KEY, 137);
        $payload = new ClobAuthPayload($signer->getAddress(), 1_234_567_890, 0, 137);

        $sig = $signer->sign($payload);

        // 0x + 32 bytes r + 32 bytes s + 1 byte v = 0x + 130 hex chars = 132 chars total
        expect($sig)->toStartWith('0x')
            ->and(strlen($sig))->toBe(132);
    });

    it('produces deterministic signatures for the same key and payload', function (): void {
        $signer = new Eip712Signer(EIP712_TEST_KEY, 137);
        $payload = new ClobAuthPayload($signer->getAddress(), 1_234_567_890, 0, 137);

        expect($signer->sign($payload))->toBe($signer->sign($payload));
    });

    it('produces different signatures for different timestamps', function (): void {
        $signer = new Eip712Signer(EIP712_TEST_KEY, 137);
        $payload1 = new ClobAuthPayload($signer->getAddress(), 1_000_000, 0, 137);
        $payload2 = new ClobAuthPayload($signer->getAddress(), 2_000_000, 0, 137);

        expect($signer->sign($payload1))->not->toBe($signer->sign($payload2));
    });

    it('produces different signatures for mainnet vs testnet chain IDs', function (): void {
        $signerMainnet = new Eip712Signer(EIP712_TEST_KEY, 137);
        $signerTestnet = new Eip712Signer(EIP712_TEST_KEY, 80002);

        $payloadMainnet = new ClobAuthPayload($signerMainnet->getAddress(), 1_000_000, 0, 137);
        $payloadTestnet = new ClobAuthPayload($signerTestnet->getAddress(), 1_000_000, 0, 80002);

        expect($signerMainnet->sign($payloadMainnet))
            ->not->toBe($signerTestnet->sign($payloadTestnet));
    });
});
