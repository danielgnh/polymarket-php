<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Http;

use Danielgnh\PolymarketPhp\Exceptions\PolymarketException;

/**
 * Fake HTTP client for testing purposes.
 * Allows setting up predefined responses without making real HTTP calls.
 */
class FakeGuzzleHttpClient implements HttpClientInterface
{
    /** @var array<string, Response> */
    private array $responses = [];

    /** @var array<string, array{method: string, path: string, data: array<string, mixed>}> */
    private array $requests = [];

    public function get(string $path, array $query = []): Response
    {
        $this->recordRequest('GET', $path, $query);

        return $this->getResponse('GET', $path);
    }

    public function post(string $path, array $data = []): Response
    {
        $this->recordRequest('POST', $path, $data);

        return $this->getResponse('POST', $path);
    }

    public function put(string $path, array $data = []): Response
    {
        $this->recordRequest('PUT', $path, $data);

        return $this->getResponse('PUT', $path);
    }

    public function delete(string $path): Response
    {
        $this->recordRequest('DELETE', $path, []);

        return $this->getResponse('DELETE', $path);
    }

    public function patch(string $path, array $data = []): Response
    {
        $this->recordRequest('PATCH', $path, $data);

        return $this->getResponse('PATCH', $path);
    }

    /**
     * Set a response for a specific method and path.
     */
    public function addResponse(string $method, string $path, Response $response): void
    {
        $key = $this->makeKey($method, $path);
        $this->responses[$key] = $response;
    }

    /**
     * Set a JSON response for a specific method and path.
     *
     * @param array<string, mixed> $data
     *
     * @throws PolymarketException
     */
    public function addJsonResponse(string $method, string $path, array $data, int $statusCode = 200): void
    {
        $body = json_encode($data);

        if ($body === false) {
            throw new PolymarketException('Failed to encode JSON response: ' . json_last_error_msg());
        }

        $response = new Response(
            statusCode: $statusCode,
            headers: ['Content-Type' => 'application/json'],
            body: $body
        );

        $this->addResponse($method, $path, $response);
    }

    /**
     * Check if a specific request was made.
     */
    public function hasRequest(string $method, string $path): bool
    {
        $key = $this->makeKey($method, $path);

        return isset($this->requests[$key]);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function recordRequest(string $method, string $path, array $data): void
    {
        $key = $this->makeKey($method, $path);
        $this->requests[$key] = [
            'method' => $method,
            'path' => $path,
            'data' => $data,
        ];
    }

    /**
     * @throws PolymarketException
     */
    private function getResponse(string $method, string $path): Response
    {
        $key = $this->makeKey($method, $path);

        if (!isset($this->responses[$key])) {
            // Return a default 404 response if no mock is set
            $body = json_encode([
                'error' => 'Not Found',
                'message' => "No fake response set for [{$method} {$path}]",
            ]);

            if ($body === false) {
                throw new PolymarketException('Failed to encode JSON response: ' . json_last_error_msg());
            }

            return new Response(
                statusCode: 404,
                headers: ['Content-Type' => 'application/json'],
                body: $body
            );
        }

        return $this->responses[$key];
    }

    private function makeKey(string $method, string $path): string
    {
        return strtoupper($method) . ' ' . $path;
    }
}
