<?php

declare(strict_types=1);

use PolymarketPhp\Polymarket\Enums\SignatureType;

describe('SignatureType', function (): void {
    it('matches the canonical CLOB V2 signature type values', function (): void {
        expect(SignatureType::EOA->value)->toBe(0)
            ->and(SignatureType::POLY_PROXY->value)->toBe(1)
            ->and(SignatureType::POLY_GNOSIS_SAFE->value)->toBe(2)
            ->and(SignatureType::POLY_1271->value)->toBe(3);
    });
});
