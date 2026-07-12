# Polymarket PHP SDK

[Polymarket API](https://polymarket.com) PHP SDK for interacting with the prediction markets and managing orders.

You can search for the markets, events, create / delete orders, send concurrent requests and much more.

[What is polymarket?](https://docs.polymarket.com/polymarket-learn/get-started/what-is-polymarket)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/polymarket-php/polymarket.svg?style=flat-square)](https://packagist.org/packages/polymarket-php/polymarket)
[![PHP Version](https://img.shields.io/packagist/php-v/polymarket-php/polymarket.svg?style=flat-square)](https://packagist.org/packages/polymarket-php/polymarket)
[![Total Downloads](https://img.shields.io/packagist/dt/polymarket-php/polymarket.svg?style=flat-square)](https://packagist.org/packages/polymarket-php/polymarket)
[![License](https://img.shields.io/packagist/l/polymarket-php/polymarket.svg?style=flat-square)](https://packagist.org/packages/polymarket-php/polymarket)
[![Tests](https://img.shields.io/github/actions/workflow/status/polymarket-php/polymarket/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/polymarket-php/polymarket/actions)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%2010-brightgreen.svg?style=flat-square)](https://phpstan.org/)

## Requirements

- PHP 8.1 or higher
- Composer

## Installation

Install the package via Composer:

```bash
composer require polymarket-php/polymarket
```

## Configuration

Add your polymarket credentials to your `.env` file:

```env
POLYMARKET_API_KEY=your-api-key
POLYMARKET_PRIVATE_KEY=0x...
```

Here is documentation [how to export you private key](https://docs.polymarket.com/polymarket-learn/FAQ/how-to-export-private-key)

## Quick Start

```php
<?php

use PolymarketPhp\Polymarket\Client;

/*
* Let's initialize the client.
* In case if you defined the POLYMARKET_API_KEY you don't need to pass any parameters in Client
*/
$client = new Client();

/*
* In case if you want to define any other API Key, you can do it as well.
*/
$client = new Client('api-key');
```

## API Architecture

Polymarket uses three separate API systems:

- **Gamma API** (`https://gamma-api.polymarket.com`) - Read-only market data
- **CLOB API** (`https://clob.polymarket.com`) - Trading operations and order management
- **Bridge API** (`https://bridge-api.polymarket.com`) - Cross-chain deposits and funding

The SDK provides separate client interfaces for each:

```php
/* Market data */
$client->gamma()->markets()->list();

/* Trading & Orders */
$client->clob()->orders()->create([...]);

/* Cross-chain deposits */
$client->bridge()->deposits()->generate([...]);
```

## API Reference

### Client Initialization

```php
use PolymarketPhp\Polymarket\Client;

/* There is a way to initialize the client with custom configuration */
$client = new Client('your-api-key', [
    'gamma_base_url' => 'https://gamma-api.polymarket.com',
    'clob_base_url' => 'https://clob.polymarket.com',
    'bridge_base_url' => 'https://bridge-api.polymarket.com',
    'timeout' => 30,
    'retries' => 3,
    'verify_ssl' => true,
]);
```

### Configuration Options

The SDK supports the following configuration options:

| Option            | Type   | Default                             | Description                                  |
|-------------------|--------|-------------------------------------|----------------------------------------------|
| `gamma_base_url`  | string | `https://gamma-api.polymarket.com`  | Gamma API base URL                           |
| `clob_base_url`   | string | `https://clob.polymarket.com`       | CLOB API base URL                            |
| `bridge_base_url` | string | `https://bridge-api.polymarket.com` | Bridge API base URL                          |
| `timeout`         | int    | `30`                                | Request timeout in seconds                   |
| `retries`         | int    | `3`                                 | Number of retry attempts for failed requests |
| `verify_ssl`      | bool   | `true`                              | Whether to verify SSL certificates           |

### Markets (Gamma API)

The Markets resource provides access to prediction market data via the Gamma API.

#### List Markets

```php
$markets = $client->gamma()->markets()->list(
    filters: ['active' => true, 'category' => 'politics'],
    limit: 100,
    offset: 0
);
```

**Parameters:**
- `filters` (array, optional): Filtering options for markets
- `limit` (int, optional): Maximum number of results (default: 100)
- `offset` (int, optional): Pagination offset (default: 0)

**Returns:** Array of market data

#### Get Market by ID

```php
$market = $client->gamma()->markets()->get('market-id');
```

**Parameters:**
- `marketId` (string): The unique identifier of the market

**Returns:** Market data array

#### Search Markets

```php
$results = $client->gamma()->markets()->search(
    query: 'election',
    filters: ['active' => true],
    limit: 50
);
```

**Parameters:**
- `query` (string): Search query string
- `filters` (array, optional): Additional filtering options
- `limit` (int, optional): Maximum number of results (default: 100)

**Returns:** Array of matching markets

### Orders (CLOB API)

The Orders resource handles order management and execution via the CLOB API.

#### List Orders

```php
$orders = $client->clob()->orders()->list(
    filters: ['status' => 'open'],
    limit: 100,
    offset: 0
);
```

**Parameters:**
- `filters` (array, optional): Filtering options for orders
- `limit` (int, optional): Maximum number of results (default: 100)
- `offset` (int, optional): Pagination offset (default: 0)

**Returns:** Array of order data

#### Get Order by ID

```php
$order = $client->clob()->orders()->get('order-id');
```

**Parameters:**
- `orderId` (string): The unique identifier of the order

**Returns:** Order data array

#### Create Order

```php
use PolymarketPhp\Polymarket\Enums\OrderSide;
use PolymarketPhp\Polymarket\Enums\OrderType;

$order = $client->clob()->orders()->create([
    'market_id' => 'market-id',
    'side' => OrderSide::BUY->value,
    'type' => OrderType::GTC->value,
    'price' => '0.52',
    'amount' => '10.00',
]);
```

**Parameters:**
- `orderData` (array): Order details including:
  - `market_id` (string): Target market identifier
  - `side` (string): Order side - use `OrderSide` enum
  - `type` (string): Order type - use `OrderType` enum
  - `price` (string): Order price as decimal string
  - `amount` (string): Order amount as decimal string

**Important:** Always use strings for price and amount values to maintain decimal precision.

**Returns:** Created order data array

#### Cancel Order

```php
$result = $client->clob()->orders()->cancel('order-id');
```

**Parameters:**
- `orderId` (string): The unique identifier of the order to cancel

**Returns:** Cancellation result data

### Bridge (Cross-Chain Deposits)

The Bridge API enables you to fund your Polymarket account from multiple blockchains including Ethereum, Arbitrum, Base, Optimism, Solana, and Bitcoin. All deposits are automatically converted to USDC.e on Polygon.

#### Get Supported Assets

Retrieve information about supported chains, tokens, and minimum deposit amounts:

```php
$assets = $client->bridge()->deposits()->supportedAssets();

// Example response structure
foreach ($assets['chains'] as $chain) {
    echo "Chain: {$chain['name']} (ID: {$chain['id']})\n";
}

foreach ($assets['tokens'] as $token) {
    echo "Token: {$token['symbol']} - Min: \${$token['minimum_usd']}\n";
}
```

**Returns:** Array containing:
- `chains` (array): List of supported blockchain networks
  - `id` (int): Chain ID
  - `name` (string): Chain name (e.g., "Ethereum", "Arbitrum")
  - `type` (string): Chain type (e.g., "evm", "solana", "bitcoin")
- `tokens` (array): List of supported tokens per chain
  - `symbol` (string): Token symbol (e.g., "USDC", "ETH")
  - `name` (string): Token full name
  - `minimum_usd` (string): Minimum deposit amount in USD
- `minimums` (array): Global minimum deposit thresholds

#### Generate Deposit Addresses

Generate unique deposit addresses for cross-chain funding:

```php
$addresses = $client->bridge()->deposits()->generate([
    'destination_address' => '0xYourPolygonWalletAddress',
    'amount_usd' => '100'
]);

// Access deposit addresses for different chains
echo "EVM Chains Address: {$addresses['evm']}\n";
echo "Solana Address: {$addresses['solana']}\n";
echo "Bitcoin Address: {$addresses['bitcoin']}\n";
```

**Parameters:**
- `depositData` (array): Deposit request data
  - `destination_address` (string, required): Your Polygon wallet address where USDC.e will be sent
  - `amount_usd` (string, required): Deposit amount in USD

**Returns:** Array of deposit addresses:
- `evm` (string): Ethereum-compatible address for EVM chains (Ethereum, Arbitrum, Base, etc.)
- `solana` (string): Solana blockchain address
- `bitcoin` (string): Bitcoin blockchain address

**Important Security Notes:**
- Always verify your destination address is correct before sending funds
- Each deposit address is unique and tied to your destination address
- Minimum deposit amounts apply (typically $10 USD equivalent)
- Test with small amounts first

#### Supported Blockchains

**EVM-Compatible Chains:**
- Ethereum Mainnet
- Arbitrum One
- Base
- Optimism
- Polygon (direct deposits)
- BNB Chain
- Avalanche C-Chain

**Other Chains:**
- Solana
- Bitcoin

#### Deposit Workflow

1. Call `supportedAssets()` to check supported tokens and minimum amounts
2. Generate deposit addresses using `generate()` with your Polygon address
3. Send assets to the provided address for your chosen blockchain
4. Bridge service automatically detects and processes the deposit
5. Assets are converted to USDC.e and sent to your Polygon address
6. You can now trade on Polymarket

**Processing Times:**
- EVM chains: ~1-5 minutes
- Solana: ~30 seconds
- Bitcoin: ~30-60 minutes

#### Complete Example

```php
use PolymarketPhp\Polymarket\Client;

$client = new Client();

// 1. Check supported assets
$assets = $client->bridge()->deposits()->supportedAssets();

echo "Supported Chains:\n";
foreach ($assets['chains'] as $chain) {
    echo "  - {$chain['name']}\n";
}

// 2. Generate deposit addresses
$addresses = $client->bridge()->deposits()->generate([
    'destination_address' => '0xYourPolygonAddress',
    'amount_usd' => '100'
]);

// 3. Display addresses to user
echo "\nDeposit Addresses:\n";
echo "Send USDC/ETH from Ethereum/Arbitrum to: {$addresses['evm']}\n";
echo "Send USDC/SOL from Solana to: {$addresses['solana']}\n";
echo "Send BTC from Bitcoin to: {$addresses['bitcoin']}\n";
```

For a complete working example, see `examples/bridge-deposit.php`.

## Error Handling

The SDK provides a comprehensive exception hierarchy for handling different error scenarios:

```php
use PolymarketPhp\Polymarket\Exceptions\{
    PolymarketException,
    AuthenticationException,
    ValidationException,
    RateLimitException,
    NotFoundException,
    ApiException
};

try {
    $market = $client->gamma()->markets()->get('invalid-id');
} catch (AuthenticationException $e) {
    // Handle 401/403 authentication errors
    echo "Authentication failed: " . $e->getMessage();
} catch (ValidationException $e) {
    // Handle 400/422 validation errors
    echo "Validation error: " . $e->getMessage();
} catch (RateLimitException $e) {
    // Handle 429 rate limit errors
    echo "Rate limit exceeded: " . $e->getMessage();
} catch (NotFoundException $e) {
    // Handle 404 not found errors
    echo "Resource not found: " . $e->getMessage();
} catch (ApiException $e) {
    // Handle other API errors (5xx)
    echo "API error: " . $e->getMessage();
} catch (PolymarketException $e) {
    // Catch-all for any SDK exception
    echo "Error: " . $e->getMessage();

    // Get additional error details
    $statusCode = $e->getCode();
    $response = $e->getResponse();
}
```

## Enums

The SDK provides type-safe enums for API fields with fixed value sets, ensuring compile-time safety and better IDE autocomplete.

### Available Enums

#### OrderSide

Specifies whether you're buying or selling shares:

```php
use PolymarketPhp\Polymarket\Enums\OrderSide;

OrderSide::BUY   // Buy shares
OrderSide::SELL  // Sell shares
```

#### OrderType

Determines the execution behavior of an order:

```php
use PolymarketPhp\Polymarket\Enums\OrderType;

OrderType::FOK  // Fill-Or-Kill: Execute immediately in full or cancel
OrderType::FAK  // Fill-And-Kill: Execute immediately for available shares, cancel remainder
OrderType::GTC  // Good-Til-Cancelled: Active until fulfilled or cancelled
OrderType::GTD  // Good-Til-Date: Active until specified date
```

#### OrderStatus

Indicates the current state of an order:

```php
use PolymarketPhp\Polymarket\Enums\OrderStatus;

OrderStatus::MATCHED    // Matched with existing order
OrderStatus::LIVE       // Resting on the order book
OrderStatus::DELAYED    // Marketable but subject to matching delay
OrderStatus::UNMATCHED  // Marketable but experiencing delay
```

#### SignatureType

For order authentication methods (canonical CLOB V2 values):

```php
use PolymarketPhp\Polymarket\Enums\SignatureType;

SignatureType::EOA               // Externally owned account (value: 0)
SignatureType::POLY_PROXY        // Polymarket proxy wallet (value: 1)
SignatureType::POLY_GNOSIS_SAFE  // Polymarket Gnosis Safe (value: 2)
SignatureType::POLY_1271         // EIP-1271 smart contract wallet (value: 3, signing not supported yet)
```

### Usage Example

```php
use PolymarketPhp\Polymarket\Enums\{OrderSide, OrderType};

$order = $client->clob()->orders()->create([
    'market_id' => 'market-id',
    'side' => OrderSide::BUY->value,
    'type' => OrderType::GTC->value,
    'price' => '0.52',
    'amount' => '10.00',
]);
```

## Working with Decimal Values

When working with financial data (prices, amounts), always use string representation to maintain precision:

```php
// Good - maintains precision
$order = $client->clob()->orders()->create([
    'price' => '0.52',
    'amount' => '10.00',
]);

// Bad - may lose precision
$order = $client->clob()->orders()->create([
    'price' => 0.52,  // Float loses precision!
    'amount' => 10.00,
]);
```

## Concurrent Requests

For improved performance when fetching multiple resources, use the concurrent methods.

### Batch Fetching

Fetch multiple items in parallel with automatic concurrency control:

```php
use PolymarketPhp\Polymarket\Client;

$client = new Client('your-api-key');

// Fetch multiple markets by ID
$marketIds = ['id-1', 'id-2', 'id-3', 'id-4', 'id-5'];
$result = $client->gamma()->markets()->getMany($marketIds, concurrency: 10);

// Iterate over successful results (BatchResult implements ArrayAccess)
foreach ($result as $id => $market) {
    echo $market['question'] . PHP_EOL;
}

// Handle any failures
if ($result->hasFailures()) {
    foreach ($result->failed as $id => $exception) {
        error_log("Failed to fetch market {$id}: {$exception->getMessage()}");
    }
}
```

### Promise-Based API

For custom workflows, use the async methods that return promises:

```php
use GuzzleHttp\Promise\Utils;

$promise1 = $client->gamma()->markets()->getAsync('market-1');
$promise2 = $client->gamma()->markets()->getAsync('market-2');
$promise3 = $client->gamma()->markets()->listAsync(['active' => true]);

// Wait for all promises
$results = Utils::unwrap([
    'market1' => $promise1,
    'market2' => $promise2,
    'list' => $promise3,
]);

echo $results['market1']['question'];
```

### Available Async Methods

**Gamma Markets:**
- `getAsync(string $marketId)` - Get single market
- `getBySlugAsync(string $slug)` - Get market by slug
- `listAsync(array $filters, int $limit, int $offset)` - List markets
- `getMany(array $marketIds, int $concurrency)` - Batch fetch by ID
- `getManyBySlug(array $slugs, int $concurrency)` - Batch fetch by slug

**CLOB Markets:**
- `getAsync(string $conditionId)` - Get single market
- `listAsync(array $params)` - List markets
- `getMany(array $conditionIds, int $concurrency)` - Batch fetch

**CLOB Orders:**
- `getAsync(string $orderId)` - Get single order
- `listAsync(array $filters, int $limit, int $offset)` - List orders
- `getOpenAsync(array $params)` - Get open orders
- `getMany(array $orderIds, int $concurrency)` - Batch fetch
- `cancelMany(array $orderIds, int $concurrency)` - Batch cancel

### Configuration

Configure default concurrency and timeout:

```php
$client = new Client('your-api-key', [
    'default_concurrency' => 10,  // Max concurrent requests (default: 10)
    'async_timeout' => 30,        // Timeout per request in seconds (default: 30)
]);
```

## Development

### Running Tests

```bash
composer test
```

### Code Style

Format code using PHP CS Fixer:

```bash
composer cs-fix
```

Check code style without making changes:

```bash
composer cs-check
```

### Static Analysis

Run PHPStan for static analysis:

```bash
composer phpstan
```

### Test Coverage

Generate test coverage report:

```bash
composer test-coverage
```

Coverage reports will be generated in the `coverage/` directory.

## Contributing

Contributions are welcome! Please follow these guidelines:

1. Follow PSR-12 coding standards
2. Write tests for new features
3. Run `composer cs-fix` before committing
4. Ensure all tests pass with `composer test`
5. Run static analysis with `composer phpstan`

## Security

If you discover any security-related issues, please email uhorman@gmail.com instead of using the issue tracker.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Credits

- **Author**: Daniel Goncharov
- **Email**: uhorman@gmail.com

## Resources

- [Polymarket Official Website](https://polymarket.com)
- [Polymarket API Documentation](https://docs.polymarket.com)
- [Package on Packagist](https://packagist.org/packages/polymarket-php/polymarket)

## Support

For bugs and feature requests, please use the [GitHub issue tracker](https://github.com/polymarket-php/polymarket/issues).
