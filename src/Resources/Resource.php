<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Resources;

use Danielgnh\PolymarketPhp\Http\HttpClientInterface;

abstract class Resource
{
    public function __construct(protected readonly HttpClientInterface $httpClient) {}
}
