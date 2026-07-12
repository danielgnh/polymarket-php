<?php

declare(strict_types=1);

namespace PolymarketPhp\Polymarket\Signing\TypedData;

interface TypedDataInterface
{
    /**
     * @return array{name: string, version: string, chainId: int, verifyingContract?: string}
     */
    public function getDomain(): array;

    /**
     * @return list<array{name: string, type: string}>
     */
    public function getDomainTypes(): array;

    /**
     * @return array<string, list<array{name: string, type: string}>>
     */
    public function getTypes(): array;

    /**
     * @return array<string, mixed>
     */
    public function getMessage(): array;

    public function getPrimaryType(): string;
}
