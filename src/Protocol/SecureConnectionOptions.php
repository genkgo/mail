<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol;

/**
 * @codeCoverageIgnore
 */
final class SecureConnectionOptions
{
    /**
     * @var float
     */
    private $timeout = 10;

    /**
     * @var int
     */
    private $method;

    /**
     * @param int $method
     */
    public function __construct(int $method)
    {
        $this->method = $method;
    }

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
     * @param int $method
     * @return SecureConnectionOptions
     */
    public function withMethod(int $method): SecureConnectionOptions
    {
        $clone = clone $this;
        $clone->method = $method;
        return $clone;
    }

    /**
     * @return float
     */
    public function getTimeout(): float
    {
        return $this->timeout;
    }

    /**
     * @return int
     */
    public function getMethod(): int
    {
        return $this->method;
    }
}
