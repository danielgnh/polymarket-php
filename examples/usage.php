<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Danielgnh\PolymarketPhp\Client;
use Danielgnh\PolymarketPhp\Enums\OrderSide;
use Danielgnh\PolymarketPhp\Enums\OrderType;
use Danielgnh\PolymarketPhp\Exceptions\PolymarketException;

$client = new Client('your-api-key-here');

try {
    // List markets
    $markets = $client->markets()->list(['active' => true], limit: 10);
    echo "Found " . count($markets) . " markets\n";

    // Get a specific market
    $market = $client->markets()->get('market-id');
    echo "Market: " . ($market['name'] ?? 'Unknown') . "\n";

    // Search markets
    $searchResults = $client->markets()->search('election', limit: 5);
    echo "Search returned " . count($searchResults) . " results\n";

    // List orders
    $orders = $client->orders()->list(limit: 10);
    echo "Found " . count($orders) . " orders\n";

    // Create an order
    $newOrder = $client->orders()->create([
        'market_id' => 'market-id',
        'side' => OrderSide::BUY->value,
        'type' => OrderType::GTC->value,
        'price' => '0.52',
        'amount' => '10.00',
    ]);
    echo "Created order: " . $newOrder['id'] . "\n";

    // Cancel an order
    $result = $client->orders()->cancel('order-id');
    echo "Order cancelled\n";

} catch (PolymarketException $e) {
	/* Handle exception */
}
