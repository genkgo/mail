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
     * @var string
     */
    private $privateKey;
    /**
     * @var resource
     */
    private $privateKeyResource;
    /**
     * @var
     */
    private $passphrase;
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
     * @param string $privateKey
     * @param string|null $passphrase
     * @throws \Exception
     */
    public function __construct(string $privateKey, string $passphrase = null)
    {
        $this->privateKey = $privateKey;
        $this->passphrase = $passphrase;
        $this->init();
    }

    /**
     * @param string $file
     * @param string|null $passphrase
     * @return Sha256Signer
     * @throws \Exception
     */
    public static function fromFile(string $file, string $passphrase = null): Sha256Signer
    {
        if (!file_exists($file)) {
            throw new InvalidPrivateKeyException('File does not exist');
        }
        return new self(file_get_contents($file), $passphrase);
    }

    /**
     * @throws InvalidPrivateKeyException
     */
    private function init(): void
    {
        if ($this->passphrase !== null) {
            $this->privateKeyResource = openssl_get_privatekey($this->privateKey, $this->passphrase);
        } else {
            $this->privateKeyResource = openssl_get_privatekey($this->privateKey);
        }

        if (!$this->privateKeyResource) {
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
        if (openssl_sign($canonicalizedHeaders, $signature, $this->privateKeyResource, $this->algorithm)) {
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