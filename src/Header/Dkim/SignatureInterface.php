<?php
declare(strict_types=1);

namespace Genkgo\Mail\Header\Dkim;
use Genkgo\Mail\Header\HeaderValueParameter;

/**
 * Interface SignInterface
 * @package Genkgo\Mail\Header\Dkim
 */
interface SignatureInterface
{
    /**
     * @param string $canonicalizedBody
     * @return string
     */
    public function hashBody(string $canonicalizedBody): string;

    /**
     * @param string $canonicalizedHeaders
     * @return string
     */
    public function signHeaders(string $canonicalizedHeaders): string;

    /**
     * @return HeaderValueParameter
     */
    public function createAlgorithmParameters(): HeaderValueParameter;

}