<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Http;

use Danielgnh\PolymarketPhp\Exceptions\JsonParseException;
use JsonException;

readonly class Response
{
    /**
     * @param array<string, mixed> $headers
     */
    public function __construct(
        private int $statusCode,
        private array $headers,
        private string $body
    ) {}

    /**
     * Decode JSON response body.
     *
     * @return array<string, mixed>
     * @throws JsonParseException
     */
    public function json(): array
    {
        try {
            return json_decode($this->body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new JsonParseException(
                message: 'Failed to parse JSON response: ' . $e->getMessage() . '. Response body: ' . substr($this->body, 0, 200),
                code: $this->statusCode,
                previous: $e
            );
        }
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    public function body(): string
    {
        return $this->body;
    }

    /**
     * @return array<string, mixed>
     */
    public function headers(): array
    {
        return $this->headers;
    }

    public function isSuccessful(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    public function header(string $name): ?string
    {
        return $this->headers[$name] ?? null;
    }
}
