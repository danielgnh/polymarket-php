<?php

declare(strict_types=1);

use PolymarketPhp\Polymarket\Enums\OrderSide;
use PolymarketPhp\Polymarket\Enums\OrderType;
use PolymarketPhp\Polymarket\Enums\SignatureType;
use PolymarketPhp\Polymarket\Exceptions\PolymarketException;
use PolymarketPhp\Polymarket\Exceptions\SigningException;
use PolymarketPhp\Polymarket\Http\FakeGuzzleHttpClient;
use PolymarketPhp\Polymarket\Resources\Clob\Orders;
use PolymarketPhp\Polymarket\Signing\Eip712Signer;

// Hardhat account #0 — a public test vector, safe to commit.
const ORDERS_POST_TEST_KEY = '0xac0974bec39a17e36ba4a6b4d238ff944bacb478cbed5efcae784d7bf4f2ff80';
const ORDERS_POST_TEST_ADDRESS = '0xf39Fd6e51aad88F6F4ce6aB8827279cffFb92266';

/**
 * Build a complete, valid post() input array using the official V2 fixture.
 *
 * @return array<string, mixed>
 */
function buildValidPostInput(OrderSide $side = OrderSide::BUY): array
{
    return [
        'order' => [
            'maker'         => ORDERS_POST_TEST_ADDRESS,
            'signer'        => ORDERS_POST_TEST_ADDRESS,
            'tokenId'       => '1234',
            'makerAmount'   => '100000000',
            'takerAmount'   => '50000000',
            'side'          => $side,
            'signatureType' => SignatureType::EOA->value,
            'salt'          => 479_249_096_354,
            'timestamp'     => '1780449126930',
        ],
        'owner'     => ORDERS_POST_TEST_ADDRESS,
        'orderType' => OrderType::GTC->value,
        'deferExec' => false,
    ];
}

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function makeOrders(?Eip712Signer $signer = null): array
{
    $fakeHttp = new FakeGuzzleHttpClient();
    $orders = new Orders($signer, $fakeHttp);

    return [$orders, $fakeHttp];
}

// ---------------------------------------------------------------------------

describe('Orders::post() – authentication guard', function (): void {
    it('throws PolymarketException when no signer is configured', function (): void {
        [$orders] = makeOrders(null);

        expect(fn () => $orders->post(buildValidPostInput()))
            ->toThrow(PolymarketException::class, 'authentication');
    });
});

describe('Orders::post() – HTTP behaviour', function (): void {
    it('sends a POST request to the /order endpoint', function (): void {
        [$orders, $fakeHttp] = makeOrders(new Eip712Signer(ORDERS_POST_TEST_KEY, 137));
        $fakeHttp->addJsonResponse('POST', '/order', ['success' => true, 'orderID' => 'abc123']);

        $result = $orders->post(buildValidPostInput());

        expect($result['success'])->toBeTrue()
            ->and($fakeHttp->hasRequest('POST', '/order'))->toBeTrue();
    });

    it('returns the decoded JSON response from the CLOB API', function (): void {
        [$orders, $fakeHttp] = makeOrders(new Eip712Signer(ORDERS_POST_TEST_KEY, 137));
        $fakeHttp->addJsonResponse('POST', '/order', ['orderID' => 'xyz-999', 'status' => 'pending']);

        $result = $orders->post(buildValidPostInput());

        expect($result)->toBe(['orderID' => 'xyz-999', 'status' => 'pending']);
    });
});

describe('Orders::post() – V2 signature', function (): void {
    it('produces the exact reference signature for the official V2 fixture', function (): void {
        [$orders, $fakeHttp] = makeOrders(new Eip712Signer(ORDERS_POST_TEST_KEY, 137));
        $fakeHttp->addJsonResponse('POST', '/order', ['success' => true]);

        $orders->post(buildValidPostInput());
        $order = $fakeHttp->getRequest('POST', '/order')['data']['order'];

        // Generated with eth-account 0.13.7 (reference implementation) for
        // the fixture above on Polygon mainnet against the V2 exchange.
        expect($order['signature'])->toBe(
            '0x745070770d383e6f4e6431858070edacbea294ed0e2b16f147127edafc51b59a'
            . '6e788aaeda02cce5989ee10cc983a4045313e1ec7075c5a5b84f6212b097d36a1b'
        );
    });

    it('signs against the neg-risk exchange when negRisk is true', function (): void {
        [$orders, $fakeHttp] = makeOrders(new Eip712Signer(ORDERS_POST_TEST_KEY, 137));
        $fakeHttp->addJsonResponse('POST', '/order', ['success' => true]);

        $input = buildValidPostInput();
        $input['negRisk'] = true;
        $orders->post($input);
        $order = $fakeHttp->getRequest('POST', '/order')['data']['order'];

        expect($order['signature'])->toBe(
            '0x0cf7ad04271cdfe92e93422d1ea1154b0c297b101f08c030f4b7555d7e63419e'
            . '5372c7b3ba494ebf1a54e9df77b1137b1f3ee76017e4c5bb0cdc8073885e194a1c'
        );
    });

    it('signs a version 3 order against the V3 exchange domain', function (): void {
        [$orders, $fakeHttp] = makeOrders(new Eip712Signer(ORDERS_POST_TEST_KEY, 137));
        $fakeHttp->addJsonResponse('POST', '/order', ['success' => true]);

        $input = buildValidPostInput();
        $input['version'] = 3;
        $orders->post($input);
        $order = $fakeHttp->getRequest('POST', '/order')['data']['order'];

        expect($order['signature'])->toBe(
            '0x1406e9ec3bcaf3101971b2b612b5680c222e61df601ccc9a9f1f37144f10f49f'
            . '408bbed7b390357803b2d1b6562a604335b790582211e76401ddbe6ae2ebc0dd1c'
        );
    });

    it('rejects POLY_1271 orders with a clear signing error', function (): void {
        [$orders] = makeOrders(new Eip712Signer(ORDERS_POST_TEST_KEY, 137));

        $input = buildValidPostInput();
        $input['order']['signatureType'] = SignatureType::POLY_1271->value;

        expect(fn () => $orders->post($input))
            ->toThrow(SigningException::class, 'POLY_1271');
    });
});

describe('Orders::post() – V2 payload shape sent to API', function (): void {
    it('sends the V2 wire format: side as string, salt as int, amounts as strings', function (): void {
        [$orders, $fakeHttp] = makeOrders(new Eip712Signer(ORDERS_POST_TEST_KEY, 137));
        $fakeHttp->addJsonResponse('POST', '/order', ['success' => true]);

        $orders->post(buildValidPostInput(OrderSide::BUY));
        $order = $fakeHttp->getRequest('POST', '/order')['data']['order'];

        expect($order['side'])->toBe('BUY')
            ->and($order['salt'])->toBe(479_249_096_354)
            ->and($order['tokenId'])->toBeString()
            ->and($order['makerAmount'])->toBeString()
            ->and($order['takerAmount'])->toBeString()
            ->and($order['timestamp'])->toBeString()
            ->and($order['expiration'])->toBe('0');
    });

    it('converts the SELL side enum to its string value', function (): void {
        [$orders, $fakeHttp] = makeOrders(new Eip712Signer(ORDERS_POST_TEST_KEY, 137));
        $fakeHttp->addJsonResponse('POST', '/order', ['success' => true]);

        $orders->post(buildValidPostInput(OrderSide::SELL));
        $order = $fakeHttp->getRequest('POST', '/order')['data']['order'];

        expect($order['side'])->toBe('SELL');
    });

    it('does not send the removed V1 fields', function (): void {
        [$orders, $fakeHttp] = makeOrders(new Eip712Signer(ORDERS_POST_TEST_KEY, 137));
        $fakeHttp->addJsonResponse('POST', '/order', ['success' => true]);

        $orders->post(buildValidPostInput());
        $order = $fakeHttp->getRequest('POST', '/order')['data']['order'];

        expect($order)->not->toHaveKey('taker')
            ->and($order)->not->toHaveKey('nonce')
            ->and($order)->not->toHaveKey('feeRateBps');
    });

    it('defaults metadata and builder to zero bytes32 in the payload', function (): void {
        [$orders, $fakeHttp] = makeOrders(new Eip712Signer(ORDERS_POST_TEST_KEY, 137));
        $fakeHttp->addJsonResponse('POST', '/order', ['success' => true]);

        $orders->post(buildValidPostInput());
        $order = $fakeHttp->getRequest('POST', '/order')['data']['order'];

        expect($order['metadata'])->toBe('0x' . str_repeat('0', 64))
            ->and($order['builder'])->toBe('0x' . str_repeat('0', 64));
    });

    it('fills salt and timestamp automatically when omitted', function (): void {
        [$orders, $fakeHttp] = makeOrders(new Eip712Signer(ORDERS_POST_TEST_KEY, 137));
        $fakeHttp->addJsonResponse('POST', '/order', ['success' => true]);

        $input = buildValidPostInput();
        unset($input['order']['salt'], $input['order']['timestamp']);
        $orders->post($input);
        $order = $fakeHttp->getRequest('POST', '/order')['data']['order'];

        // Timestamp must be now in milliseconds (13-digit range).
        expect($order['salt'])->toBeInt()->toBeGreaterThan(0)
            ->and($order['timestamp'])->toBeString()
            ->and((int) $order['timestamp'])->toBeGreaterThan(1_700_000_000_000);
    });

    it('forwards owner, orderType, deferExec, and postOnly to the top-level payload', function (): void {
        [$orders, $fakeHttp] = makeOrders(new Eip712Signer(ORDERS_POST_TEST_KEY, 137));
        $fakeHttp->addJsonResponse('POST', '/order', ['success' => true]);

        $input = buildValidPostInput();
        $input['postOnly'] = true;
        $orders->post($input);
        $payload = $fakeHttp->getRequest('POST', '/order')['data'];

        expect($payload['owner'])->toBe(ORDERS_POST_TEST_ADDRESS)
            ->and($payload['orderType'])->toBe(OrderType::GTC->value)
            ->and($payload['deferExec'])->toBeFalse()
            ->and($payload['postOnly'])->toBeTrue();
    });

    it('defaults deferExec and postOnly to false when omitted', function (): void {
        [$orders, $fakeHttp] = makeOrders(new Eip712Signer(ORDERS_POST_TEST_KEY, 137));
        $fakeHttp->addJsonResponse('POST', '/order', ['success' => true]);

        $input = buildValidPostInput();
        unset($input['deferExec']);
        $orders->post($input);

        $payload = $fakeHttp->getRequest('POST', '/order')['data'];

        expect($payload['deferExec'])->toBeFalse()
            ->and($payload['postOnly'])->toBeFalse();
    });

    it('does not leak the negRisk and version signing options into the payload', function (): void {
        [$orders, $fakeHttp] = makeOrders(new Eip712Signer(ORDERS_POST_TEST_KEY, 137));
        $fakeHttp->addJsonResponse('POST', '/order', ['success' => true]);

        $input = buildValidPostInput();
        $input['negRisk'] = true;
        $input['version'] = 2;
        $orders->post($input);
        $payload = $fakeHttp->getRequest('POST', '/order')['data'];

        expect($payload)->not->toHaveKey('negRisk')
            ->and($payload)->not->toHaveKey('version');
    });
});
