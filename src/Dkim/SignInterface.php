<?php
declare(strict_types=1);

namespace Genkgo\Mail\Dkim;

interface SignInterface
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
     * @return string
     */
    public function name(): string;
}
