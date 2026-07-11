<?php

declare(strict_types=1);

namespace PolymarketPhp\Polymarket\Resources\Clob;

use GuzzleHttp\Promise\PromiseInterface;
use PolymarketPhp\Polymarket\Enums\OrderSide;
use PolymarketPhp\Polymarket\Enums\SignatureType;
use PolymarketPhp\Polymarket\Exceptions\PolymarketException;
use PolymarketPhp\Polymarket\Exceptions\SigningException;
use PolymarketPhp\Polymarket\Http\AsyncClientInterface;
use PolymarketPhp\Polymarket\Http\BatchResult;
use PolymarketPhp\Polymarket\Http\HttpClientInterface;
use PolymarketPhp\Polymarket\Http\Response;
use PolymarketPhp\Polymarket\Resources\Resource;
use PolymarketPhp\Polymarket\Resources\Traits\HasAsyncClient;
use PolymarketPhp\Polymarket\Signing\Eip712Signer;
use PolymarketPhp\Polymarket\Signing\TypedData\OrderPayloadV2;

class Orders extends Resource
{
    use HasAsyncClient;

    public function __construct(
        private readonly ?Eip712Signer $signer,
        HttpClientInterface $httpClient,
        ?AsyncClientInterface $asyncClient = null,
    ) {
        parent::__construct($httpClient, $asyncClient);
    }

    /**
     * @param array<string, mixed> $filters
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function list(array $filters = [], int $limit = 100, int $offset = 0): array
    {
        $params = array_merge($filters, [
            'limit' => $limit,
            'offset' => $offset,
        ]);

        return $this->httpClient->get('/data/orders', $params)->json();
    }

    /**
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function get(string $orderId): array
    {
        return $this->httpClient->get("/data/order/{$orderId}")->json();
    }

    /**
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function getOpen(array $params = []): array
    {
        return $this->httpClient->get('/open-orders', $params)->json();
    }

    /**
     * @param array<string, mixed> $orderData
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function create(array $orderData): array
    {
        return $this->httpClient->post('/orders', $orderData)->json();
    }

    /**
     * Build, sign, and submit a single CLOB V2 order.
     *
     * The `order` sub-array carries the V2 EIP-712 struct fields; `salt`,
     * `timestamp` (milliseconds), `metadata`, and `builder` are filled with
     * defaults when omitted. `expiration` is no longer part of the signed
     * struct — it is forwarded to the API only (default "0"). `negRisk`
     * selects the neg-risk exchange contract and `version` (2 or 3) the
     * exchange generation; neither is sent to the API.
     *
     * @param array{order: array{maker: string, signer: string, tokenId: string|int, makerAmount: string|int, takerAmount: string|int, side: OrderSide, signatureType?: int, salt?: int|string, timestamp?: string|int, metadata?: string, builder?: string, expiration?: string|int}, owner: string, orderType: string, deferExec?: bool, postOnly?: bool, negRisk?: bool, version?: int} $inputOrderData
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function post(array $inputOrderData): array
    {
        $signer = $this->signer
            ?? throw new PolymarketException(
                'Signing requires authentication. Call Client::auth() before placing orders.'
            );

        $orderData = $inputOrderData['order'];
        $side = $orderData['side'];
        $signatureType = $orderData['signatureType'] ?? SignatureType::EOA->value;

        if ($signatureType === SignatureType::POLY_1271->value) {
            throw new SigningException(
                'POLY_1271 orders require the nested TypedDataSign flow, which is not supported yet.'
            );
        }

        $message = [
            'salt'          => $orderData['salt'] ?? random_int(1, time() * 1000),
            'maker'         => $orderData['maker'],
            'signer'        => $orderData['signer'],
            'tokenId'       => $orderData['tokenId'],
            'makerAmount'   => $orderData['makerAmount'],
            'takerAmount'   => $orderData['takerAmount'],
            'side'          => $side->forSignature(),
            'signatureType' => $signatureType,
            'timestamp'     => (string) ($orderData['timestamp'] ?? (int) (microtime(true) * 1000)),
            'metadata'      => $orderData['metadata'] ?? OrderPayloadV2::BYTES32_ZERO,
            'builder'       => $orderData['builder'] ?? OrderPayloadV2::BYTES32_ZERO,
        ];

        $signature = $signer->sign(new OrderPayloadV2(
            $message,
            $signer->getChainId(),
            $inputOrderData['negRisk'] ?? false,
            $inputOrderData['version'] ?? 2,
        ));

        return $this->httpClient->post('/order', [
            'order' => [
                'salt'          => (int) $message['salt'],
                'maker'         => $message['maker'],
                'signer'        => $message['signer'],
                'tokenId'       => (string) $message['tokenId'],
                'makerAmount'   => (string) $message['makerAmount'],
                'takerAmount'   => (string) $message['takerAmount'],
                'side'          => $side->value,
                'expiration'    => (string) ($orderData['expiration'] ?? '0'),
                'signatureType' => $signatureType,
                'timestamp'     => $message['timestamp'],
                'metadata'      => $message['metadata'],
                'builder'       => $message['builder'],
                'signature'     => $signature,
            ],
            'owner'     => $inputOrderData['owner'],
            'orderType' => $inputOrderData['orderType'],
            'deferExec' => $inputOrderData['deferExec'] ?? false,
            'postOnly'  => $inputOrderData['postOnly'] ?? false,
        ])->json();
    }

    /**
     * @param array<int, array<string, mixed>> $orders
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function postMultiple(array $orders): array
    {
        return $this->httpClient->post('/orders', $orders)->json();
    }

    /**
     * @param string|array<string, mixed> $orderIdOrPayload
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function cancel(string|array $orderIdOrPayload): array
    {
        if (is_string($orderIdOrPayload)) {
            return $this->httpClient->delete("/orders/{$orderIdOrPayload}")->json();
        }

        return $this->httpClient->delete('/order', $orderIdOrPayload)->json();
    }

    /**
     * @param array<int, string> $orderIds
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function cancelMultiple(array $orderIds): array
    {
        return $this->httpClient->delete('/orders', ['ids' => $orderIds])->json();
    }

    /**
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function cancelAll(): array
    {
        return $this->httpClient->delete('/cancel-all')->json();
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function cancelMarketOrders(array $payload): array
    {
        return $this->httpClient->delete('/cancel-market-orders', $payload)->json();
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function listAsync(array $filters = [], int $limit = 100, int $offset = 0): PromiseInterface
    {
        $params = array_merge($filters, [
            'limit' => $limit,
            'offset' => $offset,
        ]);

        return $this->getAsyncClient()->getAsync('/data/orders', $params)
            ->then(fn (Response $response): array => $response->json());
    }

    public function getAsync(string $orderId): PromiseInterface
    {
        return $this->getAsyncClient()->getAsync("/data/order/{$orderId}")
            ->then(fn (Response $response): array => $response->json());
    }

    /**
     * @param array<string, mixed> $params
     */
    public function getOpenAsync(array $params = []): PromiseInterface
    {
        return $this->getAsyncClient()->getAsync('/open-orders', $params)
            ->then(fn (Response $response): array => $response->json());
    }

    /**
     * @param array<string> $orderIds
     */
    public function getMany(array $orderIds, int $concurrency = 10): BatchResult
    {
        $promises = [];
        foreach ($orderIds as $id) {
            $promises[$id] = $this->getAsync($id);
        }

        return $this->getAsyncClient()->pool($promises, $concurrency);
    }

    /**
     * @param array<string> $orderIds
     */
    public function cancelMany(array $orderIds, int $concurrency = 5): BatchResult
    {
        $promises = [];
        foreach ($orderIds as $id) {
            $promises[$id] = $this->cancelAsync($id);
        }

        return $this->getAsyncClient()->pool($promises, $concurrency);
    }

    private function cancelAsync(string $orderId): PromiseInterface
    {
        return $this->getAsyncClient()->deleteAsync("/orders/{$orderId}")
            ->then(fn (Response $response): array => $response->json());
    }
}
