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
     * @return int
     */
    public static function getAdvisedType()
    {
        return STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
    }

    /**
     * This might be changed after https://github.com/php/php-src/pull/2518
     * @return int
     */
    public static function getSupportType()
    {
        return STREAM_CRYPTO_METHOD_TLSv1_0_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
    }

    /**
     * @return string
     */
    public static function getAdvisedProtocol()
    {
        return 'tlsv1.2://';
    }

    /**
     * @return string
     */
    public static function getSupportProtocol()
    {
        return 'tls://';
    }
}