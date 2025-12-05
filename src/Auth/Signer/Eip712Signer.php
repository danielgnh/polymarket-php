<?php

declare(strict_types=1);

namespace Danielgnh\PolymarketPhp\Auth\Signer;

use Danielgnh\PolymarketPhp\Exceptions\ClobAuthenticationException;
use Danielgnh\PolymarketPhp\Exceptions\SigningException;
use Exception;
use InvalidArgumentException;
use kornrunner\Ethereum\Address;
use kornrunner\Keccak;
use kornrunner\Secp256k1;
use kornrunner\Signature\Signature;
use Throwable;

/**
 * EIP-712 signer for CLOB authentication.
 */
class Eip712Signer
{
    private string $privateKey;

    private Address $ethAddress;

    public string $address {
        get {
            return '0x' . $this->ethAddress->get();
        }
    }

    private int $chainId;

    /**
     * @throws SigningException
     * @throws ClobAuthenticationException
     */
    public function __construct(string $privateKey, int $chainId = 137)
    {
        $this->privateKey = $this->normalizePrivateKey($privateKey);
        $this->chainId = $chainId;

        try {
            $this->ethAddress = new Address(substr($this->privateKey, 2));
        } catch (InvalidArgumentException $exception) {
            throw SigningException::eip712Failed("Failed to derive address: {$exception->getMessage()}");
        }
    }

    /**
     * Sign EIP-712 typed data for CLOB authentication.
     *
     * @param  int  $timestamp  Unix timestamp
     * @param  int  $nonce  Nonce value (default: 0)
     * @return string Signature as hex string with 0x prefix
     *
     * @throws SigningException
     */
    public function signClobAuth(int $timestamp, int $nonce = 0): string
    {
        try {
            $message = [
                'address' => strtolower($this->address),
                'timestamp' => (string) $timestamp,
                'nonce' => $nonce,
                'message' => 'This message attests that I control the given wallet',
            ];

            $domain = $this->buildDomain();
            $types = $this->buildTypes();
            $hash = $this->hashTypedData($domain, $types, $message);

            return $this->signHash($hash);
        } catch (Throwable $e) {
            throw SigningException::eip712Failed($e->getMessage());
        }
    }

    /**
     * Validate and normalize private key format (add 0x prefix if missing).
     *
     * @throws ClobAuthenticationException
     */
    private function normalizePrivateKey(string $key): string
    {
        $key = str_starts_with($key, '0x') ? substr($key, 2) : $key;

        if (!ctype_xdigit($key)) {
            throw ClobAuthenticationException::invalidPrivateKey(
                'Must be a hexadecimal string'
            );
        }

        if (strlen($key) !== 64) {
            throw ClobAuthenticationException::invalidPrivateKey(
                'Must be 32 bytes (64 hex characters)'
            );
        }

        return '0x' . strtolower($key);
    }

    /**
     * Build EIP-712 domain separator.
     *
     * @return array{name: string, version: string, chainId: int}
     */
    private function buildDomain(): array
    {
        return [
            'name' => 'ClobAuthDomain',
            'version' => '1',
            'chainId' => $this->chainId,
        ];
    }

    /**
     * Build EIP-712 type definitions.
     *
     * @return array<string, array<array{name: string, type: string}>>
     */
    private function buildTypes(): array
    {
        return [
            'ClobAuth' => [
                ['name' => 'address', 'type' => 'address'],
                ['name' => 'timestamp', 'type' => 'string'],
                ['name' => 'nonce', 'type' => 'uint256'],
                ['name' => 'message', 'type' => 'string'],
            ],
        ];
    }

    /**
     * Hash typed data according to EIP-712.
     *
     * @param array{name: string, version: string, chainId: int}                     $domain
     * @param array<string, array<array{name: string, type: string}>>                $types
     * @param array{address: string, timestamp: string, nonce: int, message: string} $message
     *
     * @return string
     * @throws Exception
     */
    private function hashTypedData(array $domain, array $types, array $message): string
    {
        // EIP-712 prefix
        $prefix = "\x19\x01";

        // Hash domain separator
        $domainHash = $this->hashStruct('EIP712Domain', [
            ['name' => 'name', 'type' => 'string'],
            ['name' => 'version', 'type' => 'string'],
            ['name' => 'chainId', 'type' => 'uint256'],
        ], $domain);

        // Hash message
        $messageHash = $this->hashStruct('ClobAuth', $types['ClobAuth'], $message);

        // Concatenate and hash: keccak256("\x19\x01" ‖ domainHash ‖ messageHash)
        $encoded = $prefix . hex2bin(substr($domainHash, 2)) . hex2bin(substr($messageHash, 2));

        return '0x' . Keccak::hash($encoded, 256);
    }

	/**
	 * Hash a struct according to EIP-712.
	 *
	 * @param string                                   $typeName
	 * @param array<array{name: string, type: string}> $types
	 * @param array<string, mixed>                     $data
	 *
	 * @return string
	 *
	 * @throws Exception
	 */
    private function hashStruct(string $typeName, array $types, array $data): string
    {
        $typeHash = $this->hashType($typeName, $types);

        $encodedValues = '';
        foreach ($types as $type) {
            $value = $data[$type['name']];
            $encodedValues .= $this->encodeValue($type['type'], $value);
        }

        // Concatenate typeHash with encoded values and hash
        $encoded = hex2bin(substr($typeHash, 2)) . hex2bin($encodedValues);

        return '0x' . Keccak::hash($encoded, 256);
    }

    /**
     * Hash a type string according to EIP-712.
     *
     * @param  array<array{name: string, type: string}>  $types
     *
     * @throws Exception
     */
    private function hashType(string $typeName, array $types): string
    {
        $typeString = $typeName . '(';
        $parts = [];
        foreach ($types as $type) {
            $parts[] = $type['type'] . ' ' . $type['name'];
        }
        $typeString .= implode(',', $parts) . ')';

        return '0x' . Keccak::hash($typeString, 256);
    }

    /**
     * Encode a value according to EIP-712 encoding rules.
     */
    private function encodeValue(string $type, mixed $value): string
    {
        if ($type === 'string') {
            return Keccak::hash($value, 256);
        }

        if ($type === 'address') {
            $address = str_replace('0x', '', $value);

            return str_pad($address, 64, '0', STR_PAD_LEFT);
        }

        if ($type === 'uint256') {
            return str_pad(dechex((int) $value), 64, '0', STR_PAD_LEFT);
        }

        throw new InvalidArgumentException("Unsupported type: $type");
    }

    /**
     * @param  string  $hash  Hash to sign (with 0x prefix)
     * @return string
     */
    private function signHash(string $hash): string
    {
        $secp256k1 = new Secp256k1();
        $privateKeyHex = substr($this->privateKey, 2);
        $hashHex = substr($hash, 2);

        /** @var Signature $signature */
        $signature = $secp256k1->sign($hashHex, $privateKeyHex);

        // Ethereum signature (r + s + v)
        $r = str_pad(gmp_strval($signature->getR(), 16), 64, '0', STR_PAD_LEFT);
        $s = str_pad(gmp_strval($signature->getS(), 16), 64, '0', STR_PAD_LEFT);
        $v = 27 + $signature->getRecoveryParam();

        return '0x' . $r . $s . dechex($v);
    }
}
