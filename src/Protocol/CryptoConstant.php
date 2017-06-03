<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol;

final class CryptoConstant
{
    /**
     *
     */
    public CONST TYPE_BEST_PRACTISE = STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
    /**
     * This might be changed after https://github.com/php/php-src/pull/2518
     */
    public CONST TYPE_SECURE = STREAM_CRYPTO_METHOD_TLSv1_0_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;

    public CONST TYPE_NONE = 0;
}