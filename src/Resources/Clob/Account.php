<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Resources\Clob;

use Danielgnh\PolymarketPhp\Exceptions\PolymarketException;
use Danielgnh\PolymarketPhp\Resources\Resource;

class Account extends Resource
{
    /**
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function getBalanceAllowance(array $params = []): array
    {
        return $this->httpClient->get('/balance-allowance', $params)->json();
    }

    /**
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function updateBalanceAllowance(array $params = []): array
    {
        return $this->httpClient->get('/update-balance-allowance', $params)->json();
    }

    /**
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function getNotifications(): array
    {
        return $this->httpClient->get('/notifications')->json();
    }

    /**
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function dropNotifications(array $params = []): array
    {
        return $this->httpClient->delete('/notifications', $params)->json();
    }

    /**
     * @return array<string, mixed>
     *
     * @throws PolymarketException
     */
    public function getClosedOnlyMode(): array
    {
        return $this->httpClient->get('/closed-only')->json();
    }
}
