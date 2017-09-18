<?php
declare(strict_types=1);

namespace Genkgo\Mail\Dkim;
use Genkgo\Mail\Exception\InvalidPrivateKeyException;

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
     * @var
     */
    private $hashHandler = 'sha256';
    /**
     * @var int
     */
    private $algorithm = OPENSSL_ALGO_SHA256;

    /**
     * Sha256Signer constructor.
     * @param string $privateKey Location of file or key string
     * @param string|null $passphrase
     * @throws \Exception
     */
    public function __construct(string $privateKey, string $passphrase = null)
    {
        if (file_exists($privateKey)) {
            if ($passphrase !== null) {
                $this->privateKey = openssl_get_privatekey(file_get_contents($privateKey), $passphrase);
            } else {
                $this->privateKey = openssl_get_privatekey(file_get_contents($privateKey));
            }
        } else {
            if ($passphrase !== null) {
                $this->privateKey = openssl_get_privatekey($privateKey, $passphrase);
            } else {
                $this->privateKey = openssl_get_privatekey($privateKey);
            }
        }

        if (!$this->privateKey) {
            throw new InvalidPrivateKeyException('Unable to load DKIM private key');
        }
    }

    /**
     * @param string $canonicalizedBody
     * @return string
     * @throws \Exception
     */
    public function hashBody(string $canonicalizedBody): string
    {
        $handler = hash_init($this->hashHandler);
        hash_update($handler, $canonicalizedBody);
        return hash_final($handler, true);
    }

    /**
     * @param string $canonicalizedHeaders
     * @return string
     * @throws \Exception
     */
    public function signHeaders(string $canonicalizedHeaders): string
    {
        if (openssl_sign($canonicalizedHeaders, $signature, $this->privateKey, $this->algorithm)) {
            return $signature;
        }
        throw new \Exception('Unable to sign DKIM Hash ['.openssl_error_string().']');
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return 'rsa-sha256';
    }
}