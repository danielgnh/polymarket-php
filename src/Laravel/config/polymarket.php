<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | API Key (Optional)
    |--------------------------------------------------------------------------
    |
    | Required for authenticated requests and higher rate limits.
    |
    */
    'api_key' => env('POLYMARKET_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Private Key (Required for Trading)
    |--------------------------------------------------------------------------
    |
    | Ethereum private key (hex format with 0x prefix) for CLOB authentication.
    | Required for write operations like creating/canceling orders.
    |
    */
    'private_key' => env('POLYMARKET_PRIVATE_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Chain ID
    |--------------------------------------------------------------------------
    |
    | Blockchain chain ID for EIP-712 signature generation.
    | Default: 137 (Polygon mainnet)
    |
    */
    'chain_id' => env('POLYMARKET_CHAIN_ID', 137),

    /*
    |--------------------------------------------------------------------------
    | API Base URLs
    |--------------------------------------------------------------------------
    |
    | Base URLs for Polymarket's two separate API systems:
    | - Gamma API: Read-only market data
    | - CLOB API: Trading operations and order management
    |
    */
    'gamma_base_url' => env('POLYMARKET_GAMMA_URL', 'https://gamma-api.polymarket.com'),
    'clob_base_url' => env('POLYMARKET_CLOB_URL', 'https://clob.polymarket.com'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Configuration
    |--------------------------------------------------------------------------
    |
    | HTTP client behavior settings.
    |
    */
    'timeout' => env('POLYMARKET_TIMEOUT', 30),
    'retries' => env('POLYMARKET_RETRIES', 3),
    'verify_ssl' => env('POLYMARKET_VERIFY_SSL', true),
];
