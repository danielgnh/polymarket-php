<?php

declare(strict_types=1);

use PolymarketPhp\Polymarket\Signing\Eip712Signer;
use PolymarketPhp\Polymarket\Signing\TypedData\OrderPayloadV2;

/**
 * Golden-value tests for V2 order signing.
 *
 * Expected signatures were generated with eth-account 0.13.7 — the reference
 * implementation used by Polymarket's official py-clob-client-v2 — for the
 * fixture below (Hardhat dev account #0, a public test vector).
 */

// Hardhat account #0 — a public test vector, safe to commit.
const GOLDEN_V2_KEY = '0xac0974bec39a17e36ba4a6b4d238ff944bacb478cbed5efcae784d7bf4f2ff80';
const GOLDEN_V2_ADDRESS = '0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266';

/**
 * The official fixture from py-clob-client-v2's builder tests.
 *
 * @return array<string, mixed>
 */
function goldenOrderDataV2(): array
{
    return [
        'salt'          => '479249096354',
        'maker'         => GOLDEN_V2_ADDRESS,
        'signer'        => GOLDEN_V2_ADDRESS,
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

describe('Eip712Signer + OrderPayloadV2 – golden signatures', function (): void {
    it('matches the reference signature on Polygon mainnet (V2 exchange)', function (): void {
        $signer = new Eip712Signer(GOLDEN_V2_KEY, 137);

        $signature = $signer->sign(new OrderPayloadV2(goldenOrderDataV2(), 137));

        expect($signature)->toBe(
            '0x745070770d383e6f4e6431858070edacbea294ed0e2b16f147127edafc51b59a'
            . '6e788aaeda02cce5989ee10cc983a4045313e1ec7075c5a5b84f6212b097d36a1b'
        );
    });

    it('matches the reference signature on Amoy testnet (V2 exchange)', function (): void {
        $signer = new Eip712Signer(GOLDEN_V2_KEY, 80002);

        $signature = $signer->sign(new OrderPayloadV2(goldenOrderDataV2(), 80002));

        expect($signature)->toBe(
            '0xa96be879ff1c5b94c1f7bbb4e253e2283748c1261f8b21390979d31ee6a16465'
            . '6132219cf49fd86574380b4f5f11232945497b29ffbc74d4bf9a0623c17af0071b'
        );
    });

    it('matches the reference signature for a neg-risk order on mainnet', function (): void {
        $signer = new Eip712Signer(GOLDEN_V2_KEY, 137);

        $signature = $signer->sign(new OrderPayloadV2(goldenOrderDataV2(), 137, negRisk: true));

        expect($signature)->toBe(
            '0x0cf7ad04271cdfe92e93422d1ea1154b0c297b101f08c030f4b7555d7e63419e'
            . '5372c7b3ba494ebf1a54e9df77b1137b1f3ee76017e4c5bb0cdc8073885e194a1c'
        );
    });

    it('matches the reference signature for a version 3 order on mainnet', function (): void {
        $signer = new Eip712Signer(GOLDEN_V2_KEY, 137);

        $signature = $signer->sign(new OrderPayloadV2(goldenOrderDataV2(), 137, version: 3));

        expect($signature)->toBe(
            '0x1406e9ec3bcaf3101971b2b612b5680c222e61df601ccc9a9f1f37144f10f49f'
            . '408bbed7b390357803b2d1b6562a604335b790582211e76401ddbe6ae2ebc0dd1c'
        );
    });
});
