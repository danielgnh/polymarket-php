# Polymarket PHP SDK - Development Instructions

## Project Overview

This is a PHP SDK for the Polymarket API - a prediction market platform. The SDK provides a clean, type-safe interface for interacting with Polymarket's REST API endpoints.

**Namespace**: `Danielgnh\PolymarketPhp`
**Minimum PHP Version**: 8.1

## Core Principles

1. **Type Safety First**: Always use strict types (`declare(strict_types=1)`) and proper type hints
2. **Immutability**: Prefer readonly properties and immutable objects where possible
3. **Simplicity**: Keep implementations simple and focused - avoid over-engineering
4. **PSR Standards**: Follow PSR-12 coding style and PSR-7/PSR-18 HTTP standards
5. **Test Coverage**: Write tests alongside implementation, not after

## Code Style & Standards

### PHP Standards
- **Always** use `declare(strict_types=1);` at the top of every PHP file
- Follow PSR-12 coding standards strictly
- Use PHP 8.1+ features: readonly properties, constructor property promotion, named arguments
- Use short array syntax: `[]` not `array()`
- Use single quotes for strings unless interpolation is needed
- **After any code changes, ALWAYS run**: `composer cs-fix` (php-cs-fixer)

### Naming Conventions
- Classes: `PascalCase`
- Methods/Functions: `camelCase`
- Constants: `UPPER_SNAKE_CASE`
- Properties: `camelCase`
- Interfaces: `PascalCase` with `Interface` suffix (e.g., `HttpClientInterface`)
- Exceptions: `PascalCase` with `Exception` suffix

### Type Hints
- Always use return type hints
- Always use parameter type hints
- Use union types when appropriate (`string|null`, not `?string` in most cases)
- Use array shapes in docblocks when returning structured arrays

## Project Architecture

### Directory Structure

```
src/
├── Client.php                    # Main SDK entry point
├── Config.php                    # Configuration management
├── Http/                         # HTTP layer abstraction
│   ├── HttpClientInterface.php
│   ├── GuzzleHttpClient.php
│   └── Response.php
├── Resources/                    # API resource services
│   ├── Markets.php              # /markets endpoints
│   ├── Orders.php               # /orders endpoints
│   └── Events.php               # /events endpoints
├── Models/                       # Data transfer objects
│   ├── Market.php
│   ├── Order.php
│   └── Event.php
├── Exceptions/                   # Custom exceptions
│   ├── PolymarketException.php  # Base exception
│   ├── AuthenticationException.php
│   ├── ValidationException.php
│   ├── RateLimitException.php
│   └── ApiException.php
└── Support/                      # Helper utilities
    ├── Arr.php                   # Array helpers
    └── Str.php                   # String helpers
```

### Key Components

#### 1. Client.php
- Main entry point for the SDK
- Factory for creating resource instances
- Manages HTTP client and configuration
- Example: `$client = new Client($apiKey); $client->markets()->list();`

#### 2. Resources (Resource Classes)
- Each resource maps to a set of API endpoints
- Extend from a base `Resource` class if needed
- Methods should be named after the action: `list()`, `get()`, `create()`, `update()`, `delete()`
- Return Models or arrays of Models
- Handle pagination, filtering, and query parameters

#### 3. Models (Data Transfer Objects)
- Represent API entities (Market, Order, Event, etc.)
- Use readonly properties with constructor property promotion
- Include a `fromArray(array $data): static` factory method
- Include a `toArray(): array` method for serialization
- Keep them simple - no business logic, just data representation

#### 4. HTTP Layer
- Abstract HTTP client to allow different implementations
- Default to Guzzle but support PSR-18 client injection
- Response wrapping for consistent error handling
- Automatic JSON encoding/decoding

#### 5. Exceptions
- All exceptions extend `PolymarketException`
- Include original API response in exception when available
- Use specific exception types for different error categories
- Include helpful error messages

## Polymarket-Specific Concerns

### 1. Decimal Precision
Polymarket deals with prices, odds, and financial data requiring precise decimal handling:
- **IMPORTANT**: Never use native `float` for prices or amounts
- Use `string` representation for decimal values in API communication
- Consider adding `brick/math` library for accurate decimal calculations if needed
- Example: Store prices as `"0.52"` (string), not `0.52` (float)

### 2. Blockchain/Crypto Integration
Polymarket uses blockchain for settlements:
- Wallet addresses should be validated and checksummed
- Consider adding Web3/Ethereum utilities if wallet signing is needed
- Handle gas estimation and transaction management if implementing write operations
- Be aware of async blockchain operations (transactions take time to confirm)

### 3. Rate Limiting
- Implement exponential backoff for rate limit responses (HTTP 429)
- Include rate limit information in responses when available
- Consider adding a RateLimiter class in Support/
- Log rate limit warnings

### 4. Real-Time Data
- Polymarket may provide WebSocket endpoints for real-time market updates
- Keep WebSocket implementation separate from REST client
- Consider using ReactPHP or similar for async WebSocket handling

### 5. Authentication
- API keys should be passed via headers (check Polymarket API docs)
- Support for wallet-based authentication (signed messages)
- Never log or expose API keys

## Error Handling

### Exception Hierarchy
```
PolymarketException
├── AuthenticationException    # 401, 403 errors
├── ValidationException        # 400, 422 errors
├── RateLimitException        # 429 errors
├── NotFoundException         # 404 errors
└── ApiException              # Other API errors (500, 502, etc.)
```

### HTTP Response Handling
- 2xx: Success - return data
- 4xx: Client error - throw appropriate exception
- 5xx: Server error - throw ApiException with retry info
- Network errors: Throw ConnectionException

### Error Response Format
Always include in exceptions:
- HTTP status code
- Error message from API
- Original response body (if available)
- Request context (endpoint, method)

## Testing Strategy

**This project uses Pest**, a delightful testing framework built on top of PHPUnit. Pest provides a more expressive and elegant syntax while maintaining full PHPUnit compatibility.

### Why Pest?
- More readable and expressive test syntax
- Less boilerplate code
- Better developer experience
- Excellent for both unit and feature tests
- Growing standard in modern PHP projects

### Test Structure

#### Unit Tests (`tests/Unit/`)
- Test individual classes in isolation
- Mock HTTP client responses
- Test error handling and edge cases
- Use Pest's dataset feature for multiple scenarios

Example:
```php
it('creates config with default values', function () {
    $config = new Config();

    expect($config->baseUrl)->toBe('https://gamma-api.polymarket.com')
        ->and($config->timeout)->toBe(30);
});
```

#### Feature Tests (`tests/Feature/`)
- Test realistic user scenarios
- Test full request/response cycles with mocked HTTP
- Test multiple components working together
- Use fixtures for realistic API responses

#### Test Fixtures (`tests/Fixtures/`)
- Store example API responses as JSON files
- Load using `$this->loadFixture('market.json')` in tests
- Keep fixtures up-to-date with actual API responses
- Use for consistent, realistic test data

### Writing Tests

#### Pest Syntax Basics
```php
// Simple test
it('does something', function () {
    expect(true)->toBeTrue();
});

// Test with description
test('user can create account', function () {
    // test code
});

// Using beforeEach/afterEach
beforeEach(function () {
    $this->client = new Client('test-key');
});

// Chaining expectations
expect($response->statusCode())->toBe(200)
    ->and($response->isSuccessful())->toBeTrue();
```

#### Common Expectations
- `toBe()` - strict equality (===)
- `toEqual()` - loose equality (==)
- `toBeTrue()` / `toBeFalse()` - boolean checks
- `toBeNull()` - null check
- `toBeInstanceOf()` - instance check
- `toThrow()` - exception check
- `toBeArray()` / `toBeString()` - type checks

#### Testing Exceptions
```php
it('throws exception on invalid json', function () {
    $response = new Response(200, [], 'invalid');
    $response->json();
})->throws(JsonParseException::class);
```

#### Using Datasets
```php
it('validates status codes', function ($code, $expected) {
    $response = new Response($code, [], '');
    expect($response->isSuccessful())->toBe($expected);
})->with([
    [200, true],
    [201, true],
    [404, false],
    [500, false],
]);
```

### Configuration Files

- `tests/Pest.php` - Global test configuration and helpers
- `phpunit.xml` - PHPUnit/Pest configuration (yes, Pest uses this)
- `tests/TestCase.php` - Base test case with shared functionality

### Commands
- Run all tests: `composer test`
- Run with coverage: `composer test-coverage`
- Run specific test: `./vendor/bin/pest tests/Unit/ConfigTest.php`
- Run specific suite: `./vendor/bin/pest --testsuite=Unit`
- Run static analysis: `composer phpstan`

### Best Practices
- Write tests alongside implementation, not after
- Use descriptive test names: `it('creates config with default values')`
- Test one concept per test
- Use fixtures for complex API responses
- Mock external dependencies (HTTP client, APIs)
- Test both success and error cases
- Keep tests fast - no actual HTTP calls in unit tests

## Common Patterns

### Resource Method Pattern
```php
public function list(array $filters = [], int $limit = 100, int $offset = 0): array
{
    $response = $this->httpClient->get('/markets', [
        'limit' => $limit,
        'offset' => $offset,
        ...$filters,
    ]);

    return array_map(
        fn(array $item) => Market::fromArray($item),
        $response->json()
    );
}
```

### Model Factory Pattern
```php
public static function fromArray(array $data): static
{
    return new static(
        id: $data['id'],
        name: $data['name'],
        description: $data['description'] ?? null,
        // ... map all fields
    );
}
```

### Configuration Pattern
```php
public readonly string $baseUrl;
public readonly ?string $apiKey;

public function __construct(?string $apiKey = null, array $options = [])
{
    $this->apiKey = $apiKey;
    $this->baseUrl = $options['base_url'] ?? 'https://gamma-api.polymarket.com';
}
```

## Documentation

### Docblocks
- Add docblocks to all public methods
- Include `@param`, `@return`, and `@throws` tags
- Describe complex behavior or edge cases
- Use array shapes for complex array returns: `@return array{id: string, name: string}`

### README.md
- Keep usage examples in README up-to-date
- Document authentication setup
- Show common use cases
- Link to Polymarket API documentation

### Code Examples
- Maintain working examples in `examples/` directory
- Include error handling in examples
- Show authentication setup

## Security Considerations

### Input Validation
- Validate all user input before making API calls
- Sanitize strings, validate numeric ranges
- Use type hints as first line of defense

### Sensitive Data
- Never log API keys or authentication tokens
- Don't include credentials in error messages
- Consider using environment variables for configuration

### API Security
- Use HTTPS for all API calls (enforced)
- Validate SSL certificates (don't disable verification)
- Implement request signing if required by Polymarket API

### Common Vulnerabilities
- Prevent command injection in any shell operations
- Avoid SQL injection (though unlikely in REST SDK)
- Sanitize any HTML if rendering API data
- Be cautious with `eval()`, `exec()`, `unserialize()` - avoid them

## Development Workflow

### Before Committing
1. Run `composer cs-fix` to format code
2. Run `composer phpstan` to check for type errors
3. Run `composer test` to ensure tests pass
4. Update tests if adding new features
5. Update docblocks and README if needed

### Adding New Endpoints
1. Create/update Resource class in `src/Resources/`
2. Create/update Model in `src/Models/`
3. Add factory method in main `Client.php`
4. Write unit tests with mocked responses
5. Add usage example to README or `examples/`
6. Document any new exceptions

### Handling API Changes
- Monitor Polymarket API changelog
- Version SDK according to semver
- Deprecate features before removing them
- Maintain backwards compatibility in minor versions

## Performance Considerations

- Use Guzzle's connection pooling for multiple requests
- Implement client-side caching for static data when appropriate
- Batch requests when API supports it
- Consider adding async request support with Promises

## Laravel Integration (Future)

If building Laravel-specific features:
- Create separate `danielgnh/polymarket-laravel` package
- Use service providers for registration
- Leverage Laravel's HTTP client wrapper
- Add Facade for convenient access
- Use config files for API credentials

## Questions to Ask When Implementing

1. **Does this need to be configurable?** - If not obvious, keep it simple
2. **What should happen on error?** - Always have a clear error handling strategy
3. **Is this data sensitive?** - Be careful with logging and error messages
4. **Does this match API documentation?** - Verify against Polymarket API docs
5. **Is decimal precision important here?** - Use strings for financial data

## References

- Polymarket API Documentation: https://docs.polymarket.com/
- PSR-12: https://www.php-fig.org/psr/psr-12/
- PSR-18 (HTTP Client): https://www.php-fig.org/psr/psr-18/
- Guzzle Documentation: https://docs.guzzlephp.org/

## Notes for Claude

- Always read existing code before modifying
- Maintain consistency with existing patterns
- Ask for clarification on ambiguous requirements
- Prioritize type safety and error handling
- Test edge cases, not just happy paths
- Keep PRs focused and atomic
- Update this CLAUDE.md if patterns evolve
