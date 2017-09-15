<?php
declare(strict_types=1);

namespace Genkgo\Mail\Dkim;

/**
 * Class Sha256Signer
 * @package Genkgo\Mail\Dkim
 */
final class Sha256Signer implements SignInterface
{
    /**
     * @var resource
     */
    private $privateKey;

    /**
     * Sha256Signer constructor.
     * @param string $privateKeyLocation
     * @param string|null $passphrase
     */
    public function __construct(string $privateKeyLocation, string $passphrase = '')
    {
        if (file_exists($privateKeyLocation)) {
            $this->privateKey = openssl_get_privatekey(file_get_contents($privateKeyLocation));
        } else {
            $this->privateKey = false;
        }
    }

    /**
     * @param string $canonicalizedBody
     * @return string
     */
    public function hashBody(string $canonicalizedBody): string
    {
        openssl_sign($canonicalizedBody, $signature, $this->privateKey, OPENSSL_ALGO_SHA256);
        return $signature;
    }

    /**
     * @param string $canonicalizedHeaders
     * @return string
     */
    public function signHeaders(string $canonicalizedHeaders): string
    {
        openssl_sign($canonicalizedHeaders, $signature, $this->privateKey, OPENSSL_ALGO_SHA256);
        return $signature;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return 'rsa-sha256';
    }

    public function getKey()
    {
        return $this->privateKey;
    }
}