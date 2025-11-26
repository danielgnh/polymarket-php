<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Http;

use Danielgnh\PolymarketPhp\Config;
use Danielgnh\PolymarketPhp\Exceptions\ApiException;
use Danielgnh\PolymarketPhp\Exceptions\AuthenticationException;
use Danielgnh\PolymarketPhp\Exceptions\NotFoundException;
use Danielgnh\PolymarketPhp\Exceptions\PolymarketException;
use Danielgnh\PolymarketPhp\Exceptions\RateLimitException;
use Danielgnh\PolymarketPhp\Exceptions\ValidationException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class GuzzleHttpClient implements HttpClientInterface
{
    private GuzzleClient $client;

    public function __construct(
        private readonly string $baseUrl,
        private readonly Config $config
    ) {
        $this->client = new GuzzleClient([
            'base_uri' => $this->baseUrl,
            'timeout' => $this->config->timeout,
            'headers' => $this->getDefaultHeaders(),
        ]);
    }

    /**
     * @throws PolymarketException
     */
    public function get(string $path, array $query = []): Response
    {
        return $this->request('GET', $path, ['query' => $query]);
    }

    /**
     * @throws PolymarketException
     */
    public function post(string $path, array $data = []): Response
    {
        return $this->request('POST', $path, ['json' => $data]);
    }

    /**
     * @throws PolymarketException
     */
    public function put(string $path, array $data = []): Response
    {
        return $this->request('PUT', $path, ['json' => $data]);
    }

    /**
     * @throws PolymarketException
     */
    public function delete(string $path): Response
    {
        return $this->request('DELETE', $path);
    }

    /**
     * @throws PolymarketException
     */
    public function patch(string $path, array $data = []): Response
    {
        return $this->request('PATCH', $path, ['json' => $data]);
    }

    /**
     * @param array<string, mixed> $options
     * @throws PolymarketException
     */
    private function request(string $method, string $path, array $options = []): Response
    {
        try {
            $response = $this->client->request($method, $path, $options);

            return $this->createResponse($response);
        } catch (GuzzleException $e) {
            $this->handleException($e);
        }
    }

    private function createResponse(ResponseInterface $response): Response
    {
        return new Response(
            statusCode: $response->getStatusCode(),
            headers: $this->normalizeHeaders($response->getHeaders()),
            body: $response->getBody()->getContents()
        );
    }

    /**
     * @param array<string, array<string>> $headers
     * @return array<string, string>
     */
    private function normalizeHeaders(array $headers): array
    {
        return array_map(function ($values) {
            return implode(', ', $values);
        }, $headers);
    }

    /**
     * @return array<string, string>
     */
    private function getDefaultHeaders(): array
    {
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'User-Agent' => 'polymarket-php-sdk/1.0',
        ];

        if ($this->config->apiKey !== null) {
            $headers['Authorization'] = 'Bearer ' . $this->config->apiKey;
        }

        return $headers;
    }

    /**
     * @param GuzzleException $e
     *
     * @return never
     * @throws ApiException
     * @throws AuthenticationException
     * @throws NotFoundException
     * @throws PolymarketException
     * @throws RateLimitException
     * @throws ValidationException
     */
    private function handleException(GuzzleException $e): never
    {
        $code = $e->getCode();
        $message = $e->getMessage();

        throw match (true) {
            $code === 401 || $code === 403 => new AuthenticationException(
                message: $message,
                code: $code
            ),
            $code === 404 => new NotFoundException(
                message: $message,
                code: $code
            ),
            $code === 422 || $code === 400 => new ValidationException(
                message: $message,
                code: $code
            ),
            $code === 429 => new RateLimitException(
                message: $message,
                code: $code
            ),
            $code >= 500 => new ApiException(
                message: 'Server error: ' . $message,
                code: $code
            ),
            default => new PolymarketException(
                message: $message,
                code: $code
            ),
        };
    }
}
