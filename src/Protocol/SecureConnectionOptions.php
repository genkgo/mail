<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol;

/**
 * Class SecureConnectionOptions
 * @package Genkgo\Mail\Protocol
 * @codeCoverageIgnore
 */
final class SecureConnectionOptions
{
    /**
     * @var float
     */
    private $timeout = 10;

    /**
     * @param float $connectionTimeout
     * @return SecureConnectionOptions
     */
    public function withTimeout(float $connectionTimeout): SecureConnectionOptions
    {
        $clone = clone $this;
        $clone->timeout = $connectionTimeout;
        return $clone;
    }

    /**
     * @return float
     */
    public function getTimeout(): float
    {
        return $this->timeout;
    }
}