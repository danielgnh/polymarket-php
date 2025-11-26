<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Exceptions;

use Exception;

class PolymarketException extends Exception
{
    /**
     * @param array<string, mixed>|null $response
     */
    public function __construct(
        string $message,
        int $code = 0,
        private readonly ?array $response = null,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getResponse(): ?array
    {
        return $this->response;
    }
}
