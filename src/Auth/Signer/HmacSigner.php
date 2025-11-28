<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Auth\Signer;

use Danielgnh\PolymarketPhp\Exceptions\SigningException;
use Throwable;

/**
 * HMAC-SHA256 signer for L2 request authentication.
 *
 * Generates HMAC signatures for CLOB API requests using the API secret.
 */
class HmacSigner
{
    /**
     * Generate HMAC signature for L2 request.
     *
     * Message format: {timestamp}{method}{path}{body}
     * Signature: Base64(HMAC-SHA256(message, urlsafe_base64_decode(secret)))
     *
     * @param string      $timestamp Unix timestamp as string
     * @param string      $method    HTTP method (GET, POST, etc.)
     * @param string      $path      Request path (e.g., /orders)
     * @param string|null $body      JSON body or null
     * @param string      $apiSecret URL-safe base64-encoded secret from CLOB API
     *
     * @return string URL-safe base64-encoded signature
     *
     * @throws SigningException
     */
    public static function sign(
        string $timestamp,
        string $method,
        string $path,
        ?string $body,
        string $apiSecret
    ): string {
        try {
            $message = $timestamp . $method . $path . ($body ?? '');
            $regularBase64Secret = strtr($apiSecret, '-_', '+/');
            $decodedSecret = base64_decode($regularBase64Secret, true);

            if ($decodedSecret === false) {
                throw SigningException::hmacFailed('Invalid base64-encoded secret');
            }

            $hmac = hash_hmac('sha256', $message, $decodedSecret, true);

            return self::encodeSignature($hmac);
        } catch (SigningException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw SigningException::hmacFailed($e->getMessage());
        }
    }

    /**
     * Encode signature as URL-safe base64.
     */
    private static function encodeSignature(string $raw): string
    {
        return strtr(base64_encode($raw), '+/', '-_');
    }
}
