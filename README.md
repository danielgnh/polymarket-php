# Polymarket PHP SDK

A modern, type-safe PHP SDK for interacting with the [Polymarket API](https://polymarket.com). Built with PHP 8.1+, this SDK provides a clean and intuitive interface for accessing prediction market data and managing orders.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/danielgnh/polymarket-php.svg?style=flat-square)](https://packagist.org/packages/danielgnh/polymarket-php)
[![PHP Version](https://img.shields.io/packagist/php-v/danielgnh/polymarket-php.svg?style=flat-square)](https://packagist.org/packages/danielgnh/polymarket-php)
[![Total Downloads](https://img.shields.io/packagist/dt/danielgnh/polymarket-php.svg?style=flat-square)](https://packagist.org/packages/danielgnh/polymarket-php)
[![License](https://img.shields.io/packagist/l/danielgnh/polymarket-php.svg?style=flat-square)](https://packagist.org/packages/danielgnh/polymarket-php)
[![Tests](https://img.shields.io/github/actions/workflow/status/danielgnh/polymarket-php/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/danielgnh/polymarket-php/actions)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%209-brightgreen.svg?style=flat-square)](https://phpstan.org/)

## Requirements

- PHP 8.1 or higher
- Composer

## Installation

Install the package via Composer:

```bash
composer require danielgnh/polymarket-php
```

## Quick Start

```php
<?php

use Danielgnh\PolymarketPhp\Client;

// Initialize the client
$client = new Client('your-api-key');

// Gamma API - Market Data
$markets = $client->gamma()->markets()->list(['active' => true], limit: 10);
$market = $client->gamma()->markets()->get('market-id');
$results = $client->gamma()->markets()->search('election');

// CLOB API - Trading Operations
$orders = $client->clob()->orders()->list(limit: 10);
```

## API Architecture

Polymarket uses two separate API systems:

- **Gamma API** (`https://gamma-api.polymarket.com`) - Read-only market data
- **CLOB API** (`https://clob.polymarket.com`) - Trading operations and order management

The SDK provides separate client interfaces for each:

```php
$client = new Client('your-api-key');

// Access Gamma API for market data
$client->gamma()->markets()->list();

// Access CLOB API for trading
$client->clob()->orders()->create([...]);
```

This separation ensures type safety and prevents accidentally calling the wrong API endpoint.

## API Reference

### Client Initialization

```php
use Danielgnh\PolymarketPhp\Client;

// Basic initialization
$client = new Client('your-api-key');

// With custom configuration
$client = new Client('your-api-key', [
    'gamma_base_url' => 'https://gamma-api.polymarket.com',
    'clob_base_url' => 'https://clob.polymarket.com',
    'timeout' => 30,
    'retries' => 3,
    'verify_ssl' => true,
]);

// Without API key (for public endpoints only)
$client = new Client();
```

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
use Danielgnh\PolymarketPhp\Enums\OrderSide;
use Danielgnh\PolymarketPhp\Enums\OrderType;

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

## Configuration Options

The SDK supports the following configuration options:

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `gamma_base_url` | string | `https://gamma-api.polymarket.com` | Gamma API base URL |
| `clob_base_url` | string | `https://clob.polymarket.com` | CLOB API base URL |
| `timeout` | int | `30` | Request timeout in seconds |
| `retries` | int | `3` | Number of retry attempts for failed requests |
| `verify_ssl` | bool | `true` | Whether to verify SSL certificates |

Example with custom configuration:

```php
$client = new Client('your-api-key', [
    'timeout' => 60,
    'retries' => 5,
    'gamma_base_url' => 'https://custom-gamma.example.com',
    'clob_base_url' => 'https://custom-clob.example.com',
]);
```

## Error Handling

The SDK provides a comprehensive exception hierarchy for handling different error scenarios:

```php
use Danielgnh\PolymarketPhp\Exceptions\{
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

### Exception Hierarchy

- `PolymarketException` - Base exception for all SDK errors
  - `AuthenticationException` - Authentication/authorization failures (401, 403)
  - `ValidationException` - Request validation errors (400, 422)
  - `RateLimitException` - Rate limit exceeded (429)
  - `NotFoundException` - Resource not found (404)
  - `ApiException` - Other API errors (5xx)
  - `JsonParseException` - JSON parsing errors

## Type-Safe Enums

The SDK provides type-safe enums for API fields with fixed value sets, ensuring compile-time safety and better IDE autocomplete.

### Available Enums

#### OrderSide

Specifies whether you're buying or selling shares:

```php
use Danielgnh\PolymarketPhp\Enums\OrderSide;

OrderSide::BUY   // Buy shares
OrderSide::SELL  // Sell shares
```

#### OrderType

Determines the execution behavior of an order:

```php
use Danielgnh\PolymarketPhp\Enums\OrderType;

OrderType::FOK  // Fill-Or-Kill: Execute immediately in full or cancel
OrderType::FAK  // Fill-And-Kill: Execute immediately for available shares, cancel remainder
OrderType::GTC  // Good-Til-Cancelled: Active until fulfilled or cancelled
OrderType::GTD  // Good-Til-Date: Active until specified date
```

#### OrderStatus

Indicates the current state of an order:

```php
use Danielgnh\PolymarketPhp\Enums\OrderStatus;

OrderStatus::MATCHED    // Matched with existing order
OrderStatus::LIVE       // Resting on the order book
OrderStatus::DELAYED    // Marketable but subject to matching delay
OrderStatus::UNMATCHED  // Marketable but experiencing delay
```

#### SignatureType

For order authentication methods:

```php
use Danielgnh\PolymarketPhp\Enums\SignatureType;

SignatureType::POLYMARKET_PROXY_EMAIL   // Email/Magic account (value: 1)
SignatureType::POLYMARKET_PROXY_WALLET  // Browser wallet (value: 2)
SignatureType::EOA                      // Externally owned account (value: 0)
```

### Usage Example

```php
use Danielgnh\PolymarketPhp\Enums\{OrderSide, OrderType};

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
- [Package on Packagist](https://packagist.org/packages/danielgnh/polymarket-php)

## Support

For bugs and feature requests, please use the [GitHub issue tracker](https://github.com/danielgnh/polymarket-php-sdk/issues).
