<?php
declare(strict_types=1);

namespace Genkgo\Mail\Dkim;

use Genkgo\Mail\Exception\FailedToSignHeadersException;

final class Sha256Signer implements SignInterface
{
    private const SIGN_ALGORITHM = OPENSSL_ALGO_SHA256;
    
    private const HASH_ALGORITHM = 'sha256';

    /**
     * @var resource
     */
    private $privateKey;

    /**
     * @param resource $key
     */
    public function __construct($key)
    {
        if (!\is_resource($key) || \get_resource_type($key) !== 'OpenSSL key') {
            throw new \InvalidArgumentException('Expected a private key resource');
        }

        $this->privateKey = $key;
    }

    /**
     * @param string $canonicalizedBody
     * @return string
     */
    public function hashBody(string $canonicalizedBody): string
    {
        return \hash(self::HASH_ALGORITHM, $canonicalizedBody, true);
    }

    /**
     * @param string $canonicalizedHeaders
     * @return string
     * @throws \Exception
     */
    public function signHeaders(string $canonicalizedHeaders): string
    {
        if (\openssl_sign($canonicalizedHeaders, $signature, $this->privateKey, self::SIGN_ALGORITHM)) {
            return $signature;
        }

        // @codeCoverageIgnoreStart
        throw new FailedToSignHeadersException(
            \sprintf('Unable to sign DKIM Hash, openssl error: %s', \openssl_error_string())
        );
        // @codeCoverageIgnoreEnd
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return 'rsa-sha256';
    }

    /**
     * @param string $file
     * @param string $passphrase
     * @return Sha256Signer
     * @throws \Exception
     */
    public static function fromFile(string $file, string $passphrase = ''): self
    {
        if (!\file_exists($file)) {
            throw new \InvalidArgumentException('File does not exist');
        }

        return self::fromString(\file_get_contents($file), $passphrase);
    }

    /**
     * @param string $privateKeyString
     * @param string $passphrase
     * @return Sha256Signer
     * @throws \Exception
     */
    public static function fromString(string $privateKeyString, string $passphrase = ''): self
    {
        $key = \openssl_pkey_get_private($privateKeyString, $passphrase);

        if ($key === false) {
            throw new \InvalidArgumentException('Cannot create resource from private key string');
        }

        return new self($key);
    }
}
