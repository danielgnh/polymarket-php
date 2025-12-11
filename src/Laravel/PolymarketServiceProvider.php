<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Laravel;

use Danielgnh\PolymarketPhp\Client;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class PolymarketServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/polymarket.php',
            'polymarket'
        );

        $this->app->singleton(Client::class, function (Application $app) {
	        $config = $app->make('config');

            return new Client(
                apiKey: $config->get('polymarket.api_key'),
                options: [
                    'gamma_base_url' => $config->get('polymarket.gamma_base_url'),
                    'clob_base_url' => $config->get('polymarket.clob_base_url'),
                    'timeout' => $config->get('polymarket.timeout'),
                    'retries' => $config->get('polymarket.retries'),
                    'verify_ssl' => $config->get('polymarket.verify_ssl'),
                    'private_key' => $config->get('polymarket.private_key'),
                    'chain_id' => $config->get('polymarket.chain_id'),
                ]
            );
        });

        $this->app->alias(Client::class, 'polymarket');
    }

    /**
     * Bootstrap the service provider.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/config/polymarket.php' => config_path('polymarket.php'),
            ], 'polymarket-config');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [Client::class, 'polymarket'];
    }
}
