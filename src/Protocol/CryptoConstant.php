<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol;

final class CryptoConstant
{
    /**
     * This might be changed after https://github.com/php/php-src/pull/2518
     * @param string $phpVersion
     * @return int
     */
    public static function getDefaultMethod(string $phpVersion)
    {
        return STREAM_CRYPTO_METHOD_TLSv1_0_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
    }
}
