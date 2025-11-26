<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Http;

use Danielgnh\PolymarketPhp\Exceptions\PolymarketException;

interface HttpClientInterface
{
    /**
     * @param array<string, mixed> $query
     *./ven
     * @throws PolymarketException
     */
    public function get(string $path, array $query = []): Response;

    /**
     * @param array<string, mixed> $data
     *
     * @throws PolymarketException
     */
    public function post(string $path, array $data = []): Response;

    /**
     * @param array<string, mixed> $data
     *
     * @throws PolymarketException
     */
    public function put(string $path, array $data = []): Response;

    /**
     * @throws PolymarketException
     */
    public function delete(string $path): Response;

    /**
     * @param array<string, mixed> $data
     *
     * @throws PolymarketException
     */
    public function patch(string $path, array $data = []): Response;
}
