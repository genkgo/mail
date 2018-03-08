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
     * @var array
     */
    private $contextOptions = [];

    /**
     * @param int $method
     * @param int $timeout
     * @param array $contextOptions
     */
    public function __construct(int $method, int $timeout = 10, array $contextOptions = [])
    {
        $this->method = $method;
        $this->timeout = $timeout;
        $this->contextOptions = $contextOptions;
    }

    /**
     * @param float $connectionTimeout
     * @return SecureConnectionOptions
     */
    public function withTimeout(float $connectionTimeout): self
    {
        $clone = clone $this;
        $clone->timeout = $connectionTimeout;
        return $clone;
    }

    /**
     * @param int $method
     * @return SecureConnectionOptions
     */
    public function withMethod(int $method): self
    {
        $clone = clone $this;
        $clone->method = $method;
        return $clone;
    }

    /**
     * @param array $contextOptions
     * @return SecureConnectionOptions
     */
    public function withContextOptions(array $contextOptions): self
    {
        $clone = clone $this;
        $clone->contextOptions = $contextOptions;
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

    /**
     * @return array
     */
    public function getContextOptions(): array
    {
        return $this->contextOptions;
    }

    /**
     * @return resource
     */
    public function createContext()
    {
        return \stream_context_create([
            'ssl' => \array_merge(
                $this->contextOptions,
                ['crypto_method' => $this->method]
            )
        ]);
    }
}
