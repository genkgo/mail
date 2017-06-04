<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol;

/**
 * Class CryptoConstant
 * @package Genkgo\Mail\Protocol
 */
final class CryptoConstant
{
    /**
     * This might be changed after https://github.com/php/php-src/pull/2518
     *
     * @param string $phpVersion
     * @return int
     */
    public static function getDefaultMethod(string $phpVersion)
    {
        return STREAM_CRYPTO_METHOD_TLSv1_0_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
    }

    /**
     * Considering that
     *
     * 1. starting from PHP 7.2.0 tls:// will negotiate best TLS version;
     * 2. pre PHP 7.2.0 tls:// is TLS 1.0 only.
     *
     * The following decisions have been made.
     *
     * 1. This library uses TLS 1.2 by default for pre PHP 7.2.0.
     * 2. Starting from PHP 7.2.0 this library uses the TLS negotiation by PHP, which
     *    means TLS 1.2 if available, otherwise falls back to a lower TLS version.
     *
     * Related: https://github.com/php/php-src/pull/2518 and https://wiki.php.net/rfc/improved-tls-constants
     *
     * @param string $phpVersion
     * @return string
     */
    public static function getDefaultProtocol(string $phpVersion)
    {
        if (version_compare($phpVersion, '7.2.0') < 0) {
            return 'tlsv1.2://';
        }

        return 'tls://';
    }
}