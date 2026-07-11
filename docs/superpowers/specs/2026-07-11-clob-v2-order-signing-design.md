# CLOB V2 Order Signing Migration — Design

Date: 2026-07-11
Branch: `feat/clob-v2-migration` (based on `pr/27`)

## Problem

Polymarket cut production over to CLOB V2 on 2026-04-28. V1-signed orders are
rejected. This SDK still signs the V1 order struct (`taker`, `expiration`,
`nonce`, `feeRateBps`) against the legacy exchange contract with EIP-712 domain
version `"1"`, so `Orders::post()` cannot place orders on production.

All facts below are verified against the official `Polymarket/py-clob-client-v2`
and `Polymarket/clob-client-v2` sources and https://docs.polymarket.com/changelog.

## V2/V3 facts (authoritative)

EIP-712 Order struct (identical for order versions 2 and 3):

```
salt uint256, maker address, signer address, tokenId uint256,
makerAmount uint256, takerAmount uint256, side uint8, signatureType uint8,
timestamp uint256, metadata bytes32, builder bytes32
```

Domain: name `Polymarket CTF Exchange`, version `"2"` or `"3"` (matches order
version), chainId, verifyingContract.

Contracts (same on Polygon 137 and Amoy 80002 unless noted):

| Contract | Address |
|---|---|
| Exchange V2 | `0xE111180000d2663C0091e4f400237545B87B996B` |
| NegRisk Exchange V2 | `0xe2222d279d744050d28e00520010520000310F59` |
| Exchange V3 (137) | `0xe3333700cA9d93003F00f0F71f8515005F6c00Aa` |
| Exchange V3 (80002) | `0x9fE6e61422AdB6F610d8597F9684b16912D50C3D` |

Selection: version 2 → negRisk ? NegRiskExchangeV2 : ExchangeV2.
Version 3 → ExchangeV3 (no neg-risk split). The active version is served by
`GET /version` on the CLOB API (official client defaults to 2 when absent).

Defaults (from the official builder): `timestamp` = now in **milliseconds**;
`metadata`/`builder` = 32 zero bytes; `expiration` = `"0"` — expiration is
**not signed**, it only appears in the JSON payload.

Signature types: `EOA=0`, `POLY_PROXY=1`, `POLY_GNOSIS_SAFE=2`, `POLY_1271=3`.
POLY_1271 uses a nested Solady `TypedDataSign` flow — out of scope; we throw a
clear `SigningException` for it.

Wire format for `POST /order`:

```json
{
  "order": {
    "salt": <int>, "maker": "0x..", "signer": "0x..", "tokenId": "..",
    "makerAmount": "..", "takerAmount": "..", "side": "BUY"|"SELL",
    "expiration": "0", "signatureType": <int>, "timestamp": "..",
    "metadata": "0x0..0", "builder": "0x0..0", "signature": "0x.."
  },
  "owner": "<apiKey>", "orderType": "GTC", "deferExec": false, "postOnly": false
}
```

ClobAuth (L1) stays at domain version `"1"` — the auth path is untouched.

## Design

Follow the existing `TypedDataInterface` pattern; keep V1 for reference,
make V2 the default path.

1. **`Signing/TypedData/OrderPayloadV2`** — new class implementing
   `TypedDataInterface`. Constructor:
   `(array $orderData, int $chainId = 137, bool $negRisk = false, int $version = 2, ?string $verifyingContract = null)`.
   Holds the V2/V3 contract constants, auto-selects the verifying contract,
   domain version = `(string) $version`. Rejects unsupported versions.
2. **`Enums/SignatureType`** — rename cases to canonical `EOA=0`,
   `POLY_PROXY=1`, `POLY_GNOSIS_SAFE=2`, add `POLY_1271=3`. (Breaking rename;
   pre-1.0 package, acceptable. Old names never shipped in a signing path.)
3. **`Orders::post()`** — build and sign the V2 struct: fill defaults
   (`salt` random, `timestamp` now-ms, `metadata`/`builder` zero-bytes32),
   sign via `Eip712Signer` with `OrderPayloadV2`, then POST the V2 wire
   format. New options: `negRisk` (default false), `postOnly` (default false),
   `version` (default 2). Throw `SigningException` for `signatureType=3`.
   `expiration` passes through to JSON only (default `"0"`).
4. **`Server::version()`** — thin `GET /version` so callers can resolve the
   active order version; SDK does not auto-resolve per-call (no hidden HTTP).
5. **`OrderPayload` (V1)** — deprecated docblock; unchanged otherwise.

## Testing

- Unit `OrderPayloadV2Test`: struct/domain shape, contract auto-selection
  matrix (chain × negRisk × version), invalid version.
- Golden-value test: sign the official fixture (Hardhat dev key
  `0xac0974…ff80`, salt `479249096354`, timestamp `1780449126930`, Amoy,
  tokenId 1234, amounts 100000000/50000000, BUY, EOA) and assert the exact
  signature produced by the reference `eth-account` implementation.
- Feature `OrdersPostTest` (V2): wire-format assertions — no `nonce`/
  `feeRateBps`/`taker` in payload, `postOnly`/`deferExec` present, side as
  string, salt as int.
- Existing V1 unit tests stay (class remains).

## Out of scope

POLY_1271 signing, batch `POST /orders` refactor (`create()`/`postMultiple()`
double-binding — separate fix), Gamma keyset pagination, Bridge withdrawals,
builder fee resolution endpoints, WebSocket/RTDS.
